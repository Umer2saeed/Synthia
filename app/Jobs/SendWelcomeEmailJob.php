<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob extends BaseJob
{
    /*
    |--------------------------------------------------------------------------
    | Constructor — inject the User model
    |--------------------------------------------------------------------------
    | We type-hint the User model here.
    | SerializesModels (from BaseJob) handles the serialization:
    |
    | WHEN DISPATCHED:
    |   $user is converted to: { "class": "App\\Models\\User", "id": 5 }
    |   This tiny JSON is stored in the jobs table payload.
    |
    | WHEN WORKER PICKS UP THE JOB:
    |   Laravel reads the id (5), runs User::find(5), and gives us
    |   a fresh User model with current database values.
    |   This ensures we always work with up-to-date data.
    |
    | WHY NOT store the full user array?
    |   The user might update their name or email between when the job
    |   was dispatched and when it runs. SerializesModels ensures we
    |   always get the latest data.
    */
    public function __construct(protected User $user) {
        /*
        |----------------------------------------------------------------------
        | Assign this job to the 'default' queue
        |----------------------------------------------------------------------
        | onQueue() from the Queueable trait tells Laravel which
        | queue channel to put this job on.
        |
        | The worker command specifies which queues to process:
        |   php artisan queue:work --queue=high,default,low
        |   This processes 'high' first, then 'default', then 'low'
        */
        $this->onQueue('default');
    }

    /*
    |--------------------------------------------------------------------------
    | handle() — The actual work the job performs
    |--------------------------------------------------------------------------
    | This method is called by the queue worker when it picks up this job.
    | Everything here runs in the background, completely separate from
    | the HTTP request that dispatched this job.
    |
    | For now we just log — we will add the actual Mailable in Task 5.
    */
    public function handle(): void
    {
        Log::info('SendWelcomeEmailJob: processing', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
        ]);

        /*
        | TODO: Task 5 will replace this log with:
        | Mail::to($this->user)->send(new WelcomeMail($this->user));
        */

        Log::info('SendWelcomeEmailJob: completed', [
            'user_id' => $this->user->id,
        ]);
    }
}
