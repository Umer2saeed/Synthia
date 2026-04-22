<?php

namespace App\Jobs;

use App\Mail\PostPublishedMail;
use App\Models\Post;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPostPublishedNotificationJob extends BaseJob
{
    /*
    | We inject the Post model.
    | SerializesModels stores only the post ID — re-fetches fresh on execution.
    | deleteWhenMissingModels = true means if the post is deleted before
    | the job runs, the job is silently discarded — no error.
    */
    public function __construct(
        protected Post $post
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        // Re-load fresh with the author relationship
        $this->post->load('user');

        $author = $this->post->user;

        // Guard: author may have been deleted
        if (!$author || !$author->email) {
            Log::warning('SendPostPublishedNotificationJob: author not found', [
                'post_id' => $this->post->id,
            ]);
            return;
        }

        /*
        | Guard: do not notify if the author published their own post.
        | This happens when an admin with author role publishes their own post.
        | In that case they already know it is published — no need to email.
        | We DO notify when an EDITOR publishes an AUTHOR's post.
        |
        | We cannot check who triggered the publish here (we are in a job).
        | Instead we use a simple rule:
        |   If post.user_id == the currently logged-in user → skip
        |   But we are in a background job — no logged-in user here.
        |   So we always send the email. The author getting a notification
        |   about their own publish is acceptable and actually useful.
        */
        Log::info('SendPostPublishedNotificationJob: sending', [
            'post_id'   => $this->post->id,
            'author_id' => $author->id,
        ]);

        Mail::to($author)->send(new PostPublishedMail($this->post));

        Log::info('SendPostPublishedNotificationJob: sent', [
            'post_id' => $this->post->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendPostPublishedNotificationJob: failed', [
            'post_id' => $this->post->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
