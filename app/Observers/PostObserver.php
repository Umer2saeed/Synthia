<?php

namespace App\Observers;

use App\Models\Follow;
use App\Models\Post;
use App\Services\BadgeService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function __construct(
        private CacheService $cache
    ) {}

    public function created(Post $post): void
    {
        $this->cache->clearPostCaches($post);
        $this->clearFeedCache($post);
        $this->clearFollowerFeedCaches($post);

        if ($post->status === 'published') {
            app(BadgeService::class)->checkAndAward($post->user);
        }
    }

    public function updated(Post $post): void
    {
        $this->cache->clearPostCaches($post);
        $this->clearFeedCache($post);
        $this->clearFollowerFeedCaches($post);

        /*
        | Check badges when a post becomes published.
        | getOriginal('status') is the value BEFORE the update.
        */
        if ($post->wasChanged('status') && $post->status === 'published') {
            app(BadgeService::class)->checkAndAward($post->user);
        }
    }

    public function deleted(Post $post): void
    {
        $this->cache->clearPostCaches($post);
        $this->clearFeedCache($post);
        $this->clearFollowerFeedCaches($post);
    }

    public function restored(Post $post): void
    {
        $this->cache->clearPostCaches($post);
        $this->clearFeedCache($post);
        $this->clearFollowerFeedCaches($post);
    }

    /*
    | Clear RSS feed cache when post changes.
    */
    private function clearFeedCache(Post $post): void
    {
        Cache::forget('rss.feed.main.ids');

        if ($post->category_id) {
            Cache::forget('rss.feed.category.ids.' . $post->category_id);
        }
    }

    /*
    | Clear activity feed cache for every follower of this post's author.
    |
    | WHY clear all pages?
    | We use a pattern-based approach. Since file cache does not support
    | tag-based flushing, we store a "version" key per user and bump it.
    | A simpler approach: clear page 1 only (most users land on page 1).
    | For now we clear pages 1 through 5 — covers 99% of users.
    */
    private function clearFollowerFeedCaches(Post $post): void
    {
        /*
        | Only published posts affect the activity feed.
        */
        if ($post->status !== 'published') {
            return;
        }

        /*
        | Get all followers of this post's author.
        */
        $followerIds = Follow::where('following_id', $post->user_id)
            ->pluck('follower_id')
            ->toArray();

        foreach ($followerIds as $followerId) {
            /*
            | Clear the first 5 pages of each follower's feed cache.
            */
            for ($page = 1; $page <= 5; $page++) {
                Cache::forget('feed.user.' . $followerId . '.page.' . $page);
            }
        }
    }
}
