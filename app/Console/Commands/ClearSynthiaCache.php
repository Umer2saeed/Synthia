<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use App\Support\CacheKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSynthiaCache extends Command
{
    protected $signature   = 'synthia:cache-clear {--type=all}';
    protected $description = 'Clear Synthia application cache. Options: all, sidebar, posts, dashboard';

    public function __construct(
        private CacheService $cache
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->option('type');

        match ($type) {
            'sidebar'   => $this->clearSidebar(),
            'posts'     => $this->clearPosts(),
            'dashboard' => $this->clearDashboard(),
            default     => $this->clearAll(),
        };

        return Command::SUCCESS;
    }

    private function clearSidebar(): void
    {
        $this->cache->clearSidebarCaches();
        $this->info('Sidebar caches cleared. (sidebar_categories, sidebar_tags)');
    }

    private function clearPosts(): void
    {
        Cache::forget(CacheKeys::HOME_FEATURED_POSTS);
        Cache::forget(CacheKeys::HOME_LATEST_POSTS);

        for ($i = 1; $i <= 20; $i++) {
            Cache::forget(CacheKeys::blogPage($i));
        }

        $this->info('Post caches cleared. (home, blog listing pages 1-20)');
    }

    private function clearDashboard(): void
    {
        $this->cache->clearDashboardCache();
        $this->info('Dashboard cache cleared. (dashboard_stats)');
    }

    private function clearAll(): void
    {
        $this->cache->clearAll();
        $this->info('All Synthia caches cleared.');
    }
}
