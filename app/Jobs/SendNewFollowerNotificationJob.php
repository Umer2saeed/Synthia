<?php

namespace App\Jobs;

use App\Mail\NewFollowerMail;
use App\Models\Follow;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewFollowerNotificationJob extends BaseJob
{
    public function __construct(
        protected Follow $follow
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $this->follow->load(['follower', 'following']);

        $author   = $this->follow->following;  // person being followed
        $follower = $this->follow->follower;   // person who followed

        if (!$author || !$follower || !$author->email) {
            return;
        }

        // Do not notify if somehow following themselves
        if ($author->id === $follower->id) {
            return;
        }

        Log::info('SendNewFollowerNotificationJob: sending', [
            'author_id'   => $author->id,
            'follower_id' => $follower->id,
        ]);

        Mail::to($author)->send(new NewFollowerMail($this->follow));

        Log::info('SendNewFollowerNotificationJob: sent', [
            'author_id' => $author->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendNewFollowerNotificationJob: failed', [
            'follow_id' => $this->follow->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
