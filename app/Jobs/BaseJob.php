<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Traits — the four required traits for Laravel queue jobs
    |--------------------------------------------------------------------------
    |
    | Dispatchable     → adds the static dispatch() and dispatchIf() methods
    |                    Usage: MyJob::dispatch($data)
    |
    | InteractsWithQueue → adds methods like $this->fail(), $this->release()
    |                      Allows the job to interact with the queue system
    |
    | Queueable        → adds the onQueue(), onConnection(), delay() methods
    |                    Usage: MyJob::dispatch()->onQueue('high')
    |
    | SerializesModels → when an Eloquent model is stored in a job property,
    |                    this trait serializes it as just the model ID.
    |                    When the worker picks up the job, it re-fetches
    |                    the model from the database using that ID.
    |                    WHY: storing the full model in the job payload
    |                    would cause stale data if the model changed
    |                    between dispatch and execution.
    */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | $tries — Maximum number of attempts
    |--------------------------------------------------------------------------
    | If a job fails, Laravel retries it this many times before giving up
    | and moving it to the failed_jobs table.
    |
    | 3 tries is a good balance:
    |   - Handles temporary network issues (SMTP server briefly down)
    |   - Does not retry forever on permanent errors (invalid email)
    */
    public int $tries = 3;

    /*
    |--------------------------------------------------------------------------
    | $timeout — Maximum seconds a job can run
    |--------------------------------------------------------------------------
    | If a job takes longer than this, it is killed and marked as failed.
    | 60 seconds is generous for sending a single email.
    | Prevents jobs from hanging forever on network timeouts.
    */
    public int $timeout = 60;

    /*
    |--------------------------------------------------------------------------
    | $backoff — Seconds to wait before retrying a failed job
    |--------------------------------------------------------------------------
    | Array means progressive backoff:
    |   First retry:  wait 30 seconds
    |   Second retry: wait 60 seconds
    |   Third retry:  wait 120 seconds
    |
    | Progressive backoff prevents hammering a failing service.
    | If the SMTP server is down, waiting longer each time gives
    | it more time to recover before the next attempt.
    */
    public array $backoff = [30, 60, 120];

    /*
    |--------------------------------------------------------------------------
    | $deleteWhenMissingModels — Delete job if model no longer exists
    |--------------------------------------------------------------------------
    | If a job is queued to send an email to User ID 5, but User 5
    | is deleted before the worker processes the job, what happens?
    |
    | true  → job is silently deleted (no error, no failed_jobs entry)
    | false → job fails with ModelNotFoundException
    |
    | true is the correct setting for notification emails —
    | no point sending an email to a deleted user.
    */
    public bool $deleteWhenMissingModels = true;

    /*
    |--------------------------------------------------------------------------
    | failed() — Called when job exhausts all retry attempts
    |--------------------------------------------------------------------------
    | Override this in child jobs for custom failure handling.
    | By default we just log the error.
    |
    | @param \Throwable $exception The exception that caused the failure
    */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Queue job failed', [
            'job'       => static::class,
            'exception' => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
