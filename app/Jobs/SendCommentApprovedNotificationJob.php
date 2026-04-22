<?php

namespace App\Jobs;

use App\Mail\CommentApprovedMail;
use App\Models\Comment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCommentApprovedNotificationJob extends BaseJob
{
    /*
    | Inject the Comment model.
    | SerializesModels re-fetches fresh from DB when job runs.
    | deleteWhenMissingModels = true handles deleted comments silently.
    */
    public function __construct(
        protected Comment $comment
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $this->comment->load(['user', 'post']);

        $commenter = $this->comment->user;
        $post      = $this->comment->post;

        // Guard: commenter or post may have been deleted
        if (!$commenter || !$post || !$commenter->email) {
            Log::warning('SendCommentApprovedNotificationJob: missing data', [
                'comment_id' => $this->comment->id,
            ]);
            return;
        }

        // Guard: comment must still be approved when job runs
        // (admin may have unapproved it between dispatch and execution)
        if (!$this->comment->is_approved) {
            return;
        }

        Log::info('SendCommentApprovedNotificationJob: sending', [
            'comment_id'   => $this->comment->id,
            'commenter_id' => $commenter->id,
        ]);

        Mail::to($commenter)->send(new CommentApprovedMail($this->comment));

        Log::info('SendCommentApprovedNotificationJob: sent', [
            'comment_id' => $this->comment->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendCommentApprovedNotificationJob: failed', [
            'comment_id' => $this->comment->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
