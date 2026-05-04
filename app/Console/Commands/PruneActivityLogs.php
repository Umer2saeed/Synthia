<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature   = 'activity:prune';
    protected $description = 'Delete activity logs older than 90 days';

    public function handle(): int
    {
        $cutoff  = now()->subDays(90);
        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} activity log entries older than 90 days.");

        return Command::SUCCESS;
    }
}
