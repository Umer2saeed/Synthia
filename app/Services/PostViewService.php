<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostViewService
{
    /*
    |--------------------------------------------------------------------------
    | HOW THE VIEW THROTTLE WORKS
    |--------------------------------------------------------------------------
    |
    | When a reader visits a post we build a unique cache key:
    |   "post_view_5_192.168.1.1"  (post_id + visitor IP)
    |
    | We check if this key exists in cache:
    |   - Key EXISTS  → this IP already viewed this post in last 24h → skip
    |   - Key MISSING → first view from this IP today → count it
    |
    | After counting we store the key for 24 hours.
    | Next visit from same IP within 24h → key exists → not counted.
    |
    | WHY IP-based and not session-based?
    | Sessions are tied to a browser tab and reset on clear.
    | IP is more reliable for throttling — a real human has one IP.
    | Bots often rotate IPs but we cannot perfectly stop all bots.
    | This simple throttle stops accidental refresh spam which is
    | the most common source of inflated view counts.
    */

    /*
    |--------------------------------------------------------------------------
    | record() — Record a view for this post from this request
    |--------------------------------------------------------------------------
    | Call this in the controller when a post page is rendered.
    | Returns true if the view was counted, false if throttled.
    */
    public function record(Post $post, Request $request): bool
    {
        /*
        | Build the throttle key using post ID and visitor IP.
        | We hash the IP for privacy — we do not store raw IPs.
        */
        $ip       = $request->ip();
        $key      = $this->buildThrottleKey($post->id, $ip);

        /*
        | If the throttle key exists, this IP already viewed
        | this post within the last 24 hours. Do not count it.
        */
        if (Cache::has($key)) {
            return false;
        }

        /*
        | First view from this IP today.
        | 1. Store the throttle key for 24 hours
        | 2. Increment the view count in the database
        | 3. Clear the cached view count so it refreshes
        */
        Cache::put($key, true, now()->addHours(24));

        /*
        | increment() runs: UPDATE posts SET views = views + 1 WHERE id = X
        | This is atomic — safe even with concurrent visitors.
        | We do NOT use $post->update(['views' => $post->views + 1])
        | because that creates a race condition with simultaneous visitors.
        */
        $post->increment('views');

        /*
        | Clear the cached view count for this post so the next
        | read gets the fresh value from the database.
        */
        Cache::forget($this->buildViewCacheKey($post->id));

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | getViewCount() — Get view count with caching
    |--------------------------------------------------------------------------
    | Reading the view count from DB on every page load is wasteful.
    | We cache it for 5 minutes — good enough for display purposes.
    | The cache is cleared when a new view is recorded (above).
    */
    public function getViewCount(Post $post): int
    {
        return (int) Cache::remember(
            $this->buildViewCacheKey($post->id),
            now()->addMinutes(5),
            fn () => $post->fresh()->views ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | buildThrottleKey() — Unique key per post per visitor per day
    |--------------------------------------------------------------------------
    */
    private function buildThrottleKey(int $postId, string $ip): string
    {
        /*
        | We hash the IP with md5 so we do not store raw IP addresses
        | in our cache. Privacy-conscious and still unique per IP.
        */
        return 'post_view_' . $postId . '_' . md5($ip);
    }

    /*
    |--------------------------------------------------------------------------
    | buildViewCacheKey() — Cache key for the view count itself
    |--------------------------------------------------------------------------
    */
    private function buildViewCacheKey(int $postId): string
    {
        return 'post_views_count_' . $postId;
    }
}
