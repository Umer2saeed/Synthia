<?php

namespace App\Observers;

use App\Models\Tag;
use App\Services\CacheService;

class TagObserver
{
    public function __construct(
        private CacheService $cache
    ) {}

    public function created(Tag $tag): void
    {
        $this->cache->clearSidebarCaches();
        $this->cache->clearDashboardCache();
    }

    public function updated(Tag $tag): void
    {
        $this->cache->clearSidebarCaches();
    }

    public function deleted(Tag $tag): void
    {
        $this->cache->clearSidebarCaches();
        $this->cache->clearDashboardCache();
    }
}
