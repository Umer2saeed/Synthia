<?php

namespace App\Jobs;

use App\Mail\WeeklyDigestMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWeeklyDigestJob extends BaseJob
{
    /*
    | WHY inject the collections directly instead of re-querying in handle()?
    | The digest content is the same for all users.
    | Building it in the command and passing it to each job means:
    |   - Database queries run ONCE in the command
    |   - 500 jobs do NOT each run the same 4 queries
    |   - Much faster and less database load
    |
    | Collections are serialized to JSON in the jobs table payload.
    | They are deserialized back to collections when the job runs.
    */
    public function __construct(
        protected User       $user,
        protected Collection $topPosts,
        protected Collection $newPosts,
        protected array      $stats,
        protected Collection $newAuthors
    ) {
        $this->onQueue('low'); // digest is low priority
    }

    public function handle(): void
    {
        // Skip if user was deactivated or deleted between dispatch and execution
        if (!$this->user->email || $this->user->status !== 'active') {
            return;
        }

        Log::info('SendWeeklyDigestJob: sending', [
            'user_id' => $this->user->id,
        ]);

        Mail::to($this->user)->send(
            new WeeklyDigestMail(
                $this->user,
                $this->topPosts,
                $this->newPosts,
                $this->stats,
                $this->newAuthors
            )
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWeeklyDigestJob: failed', [
            'user_id' => $this->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
