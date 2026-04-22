<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;

class CategoryObserver
{
    public function __construct(
        private CacheService $cache
    ) {}

    /*
    | Any change to categories affects the sidebar category list.
    | Clear sidebar cache on every create/update/delete.
    */
    public function created(Category $category): void
    {
        $this->cache->clearSidebarCaches();
        $this->cache->clearDashboardCache();
    }

    public function updated(Category $category): void
    {
        $this->cache->clearSidebarCaches();
    }

    public function deleted(Category $category): void
    {
        $this->cache->clearSidebarCaches();
        $this->cache->clearDashboardCache();
    }
}
