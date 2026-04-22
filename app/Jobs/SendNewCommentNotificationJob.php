<?php

namespace App\Jobs;

use App\Mail\NewCommentMail;
use App\Models\Comment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewCommentNotificationJob extends BaseJob
{
    /*
    | We inject the Comment model.
    | SerializesModels stores only the ID — re-fetches on execution.
    | deleteWhenMissingModels = true (from BaseJob) handles deleted comments.
    */
    public function __construct(
        protected Comment $comment
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        // Re-load relationships fresh from DB
        $this->comment->load(['post.user', 'user']);

        $postAuthor = $this->comment->post?->user;

        // Do not send if post or author no longer exists
        if (!$postAuthor) {
            Log::warning('SendNewCommentNotificationJob: post author not found', [
                'comment_id' => $this->comment->id,
            ]);
            return;
        }

        // Do not notify author if they commented on their own post
        if ($postAuthor->id === $this->comment->user_id) {
            return;
        }

        // Do not send if author has no email
        if (!$postAuthor->email) {
            return;
        }

        Log::info('SendNewCommentNotificationJob: sending', [
            'comment_id'  => $this->comment->id,
            'post_author' => $postAuthor->email,
        ]);

        Mail::to($postAuthor)->send(new NewCommentMail($this->comment));

        Log::info('SendNewCommentNotificationJob: sent', [
            'comment_id' => $this->comment->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendNewCommentNotificationJob: failed', [
            'comment_id' => $this->comment->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
