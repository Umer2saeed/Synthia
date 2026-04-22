<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob extends BaseJob
{
    public function __construct(
        protected User $user
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        Log::info('SendWelcomeEmailJob: starting', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
        ]);

        Mail::to($this->user)->send(new WelcomeMail($this->user));

        Log::info('SendWelcomeEmailJob: completed', [
            'user_id' => $this->user->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWelcomeEmailJob: permanently failed', [
            'user_id'   => $this->user->id,
            'email'     => $this->user->email,
            'exception' => $exception->getMessage(),
        ]);
    }
}
