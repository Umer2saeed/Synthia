<?php

namespace App\Jobs;

use App\Mail\AccountStatusChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAccountStatusChangedJob extends BaseJob
{
    /*
    | We inject both the User model and the new status string.
    | WHY pass status separately?
    | SerializesModels re-fetches the user fresh from DB when the job runs.
    | If the admin toggles the status twice quickly, the re-fetched user
    | might have a different status than when we dispatched.
    | Passing the status at dispatch time preserves what we intended to notify.
    */
    public function __construct(
        protected User   $user,
        protected string $newStatus
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        // Re-fetch fresh — SerializesModels handles this
        if (!$this->user->email) {
            return;
        }

        Log::info('SendAccountStatusChangedJob: sending', [
            'user_id'    => $this->user->id,
            'new_status' => $this->newStatus,
            'user_status_in_db' => $this->user->status, // log both for debugging
        ]);

        Mail::to($this->user)->send(
            new AccountStatusChangedMail($this->user, $this->newStatus)
        );

        Log::info('SendAccountStatusChangedJob: sent', [
            'user_id' => $this->user->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendAccountStatusChangedJob: failed', [
            'user_id' => $this->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
