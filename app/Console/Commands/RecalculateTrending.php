<?php

namespace App\Console\Commands;

use App\Services\TrendingService;
use Illuminate\Console\Command;

class RecalculateTrending extends Command
{
    protected $signature   = 'trending:recalculate';
    protected $description = 'Recalculate trending post scores';

    public function handle(TrendingService $trendingService): int
    {
        $this->info('Recalculating trending scores...');

        $count = $trendingService->recalculate();

        $this->info("Done. Processed {$count} posts.");

        return Command::SUCCESS;
    }
}
