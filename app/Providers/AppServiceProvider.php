<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        /*
        |----------------------------------------------------------------------
        | Set Tailwind CSS as the default pagination view
        |----------------------------------------------------------------------
        | By default Laravel uses Bootstrap pagination. This switches it to
        | the Tailwind view we published above so all paginator calls across
        | the entire app — admin panel and frontend — use the same styling.
        |
        | This means every ->paginate() call automatically uses this view
        | without needing to call ->links('vendor.pagination.tailwind')
        | manually on each page.
        */
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        /*
        |----------------------------------------------------------------------
        | Queue Event Listeners
        |----------------------------------------------------------------------
        | These listeners fire on queue events and write to the log file.
        | This gives you visibility into what the queue worker is doing
        | without having to watch the terminal output.
        |
        | Logs appear in: storage/logs/laravel.log
        */

        /*
        | Fired just BEFORE a job starts processing.
        | Useful for knowing which job the worker picked up.
        */
        Queue::before(function (JobProcessing $event) {
            Log::info('Queue: job starting', [
                'job'        => $event->job->resolveName(),
                'queue'      => $event->job->getQueue(),
                'attempt'    => $event->job->attempts(),
            ]);
        });

        /*
        | Fired just AFTER a job completes successfully.
        | Useful for confirming jobs are completing.
        */
        Queue::after(function (JobProcessed $event) {
            Log::info('Queue: job completed', [
                'job'   => $event->job->resolveName(),
                'queue' => $event->job->getQueue(),
            ]);
        });

        /*
        | Fired when a job fails after exhausting all retries.
        | This is where you would send an alert to yourself in production.
        */
        Queue::failing(function (JobFailed $event) {
            Log::error('Queue: job failed permanently', [
                'job'       => $event->job->resolveName(),
                'queue'     => $event->job->getQueue(),
                'exception' => $event->exception->getMessage(),
            ]);
        });

    }
}
