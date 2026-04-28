<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature   = 'cache:warm';
    protected $description = 'Pre-warm all application caches';

    public function handle(CacheService $cache): int
    {
        $this->info('Warming caches...');

        $start = microtime(true);

        $cache->getSidebarCategories();
        $this->line('  ✓ Sidebar categories');

        $cache->getSidebarTags();
        $this->line('  ✓ Sidebar tags');

        $cache->getFeaturedPosts();
        $this->line('  ✓ Featured posts');

        $cache->getLatestPosts();
        $this->line('  ✓ Latest posts');

        $ms = round((microtime(true) - $start) * 1000);
        $this->info("Done in {$ms}ms — all caches warmed.");

        return Command::SUCCESS;
    }
}
