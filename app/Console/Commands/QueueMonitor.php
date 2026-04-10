<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Command
{
    protected $signature   = 'queue:monitor-synthia';
    protected $description = 'Show Synthia queue statistics';

    public function handle(): int
    {
        $this->info('=== Synthia Queue Monitor ===');
        $this->newLine();

        /*
        |----------------------------------------------------------------------
        | Pending jobs count
        |----------------------------------------------------------------------
        | Jobs that are waiting to be picked up by a worker.
        | reserved_at IS NULL means no worker has claimed them yet.
        */
        $pending = DB::table('jobs')
            ->whereNull('reserved_at')
            ->count();

        /*
        |----------------------------------------------------------------------
        | Reserved jobs count
        |----------------------------------------------------------------------
        | Jobs that a worker has picked up and is currently processing.
        */
        $reserved = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->count();

        /*
        |----------------------------------------------------------------------
        | Failed jobs count
        |----------------------------------------------------------------------
        | Jobs that failed after exhausting all retry attempts.
        */
        $failed = DB::table('failed_jobs')->count();

        /*
        |----------------------------------------------------------------------
        | Jobs by queue channel
        |----------------------------------------------------------------------
        | Break down pending jobs by which queue they are in.
        */
        $byQueue = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->get();

        // Display in a formatted table
        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending Jobs',  $pending],
                ['Reserved Jobs', $reserved],
                ['Failed Jobs',   $failed],
            ]
        );

        if ($byQueue->isNotEmpty()) {
            $this->newLine();
            $this->info('Jobs by Queue:');
            $this->table(
                ['Queue', 'Pending'],
                $byQueue->map(fn($row) => [$row->queue, $row->count])->toArray()
            );
        }

        if ($failed > 0) {
            $this->newLine();
            $this->warn("⚠ You have {$failed} failed jobs. Run: php artisan queue:failed");
        }

        // Show recent failed jobs
        $recentFailed = DB::table('failed_jobs')
            ->latest('failed_at')
            ->limit(5)
            ->get(['id', 'queue', 'failed_at', 'exception']);

        if ($recentFailed->isNotEmpty()) {
            $this->newLine();
            $this->error('Recent Failed Jobs:');
            foreach ($recentFailed as $job) {
                $exception = explode("\n", $job->exception)[0]; // first line only
                $this->line("  [{$job->id}] Queue: {$job->queue} | Failed: {$job->failed_at}");
                $this->line("  Error: " . \Str::limit($exception, 100));
                $this->newLine();
            }
        }

        return Command::SUCCESS;
    }
}
