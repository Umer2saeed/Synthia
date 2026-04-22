<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\CacheService;

class PostObserver
{
    public function __construct(
        private CacheService $cache
    ) {}

    /*
    | Fires after a new post is inserted into the database.
    | New post → home page is stale → blog listing is stale.
    */
    public function created(Post $post): void
    {
        $this->cache->clearPostCaches($post);
    }

    /*
    | Fires after an existing post record is updated.
    | Covers: status changes, content edits, slug changes, featured toggle.
    */
    public function updated(Post $post): void
    {
        $this->cache->clearPostCaches($post);
    }

    /*
    | Fires after soft delete.
    | Deleted post must not appear in cached lists.
    */
    public function deleted(Post $post): void
    {
        $this->cache->clearPostCaches($post);
    }

    /*
    | Fires after restoring a soft-deleted post.
    | Restored post should appear in lists again.
    */
    public function restored(Post $post): void
    {
        $this->cache->clearPostCaches($post);
    }
}
