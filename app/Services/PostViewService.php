<?php

namespace App\Services;

use App\Models\Post;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostViewService
{
    public function record(Post $post, Request $request): bool
    {
        $ip  = $request->ip();
        $key = $this->buildThrottleKey($post->id, $ip);

        if (Cache::has($key)) {
            /*
            | Already counted within 24h — but still update reading history.
            | A user revisiting a post should update their "last read" time
            | even if the view count is throttled.
            */
            $this->recordReadingHistory($post);
            return false;
        }

        Cache::put($key, true, now()->addHours(24));
        $post->increment('views');
        Cache::forget($this->buildViewCacheKey($post->id));

        $this->recordReadingHistory($post);

        return true;
    }

    /*
    | Upsert a reading history row for the authenticated user.
    | Guests do not get reading history — no user_id available.
    */
    private function recordReadingHistory(Post $post): void
    {
        if (!auth()->check()) {
            return;
        }

        ReadingHistory::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    public function getViewCount(Post $post): int
    {
        return (int) Cache::remember(
            $this->buildViewCacheKey($post->id),
            now()->addMinutes(5),
            fn() => $post->fresh()->views ?? 0
        );
    }

    private function buildThrottleKey(int $postId, string $ip): string
    {
        return 'post_view_' . $postId . '_' . md5($ip);
    }

    private function buildViewCacheKey(int $postId): string
    {
        return 'post_views_count_' . $postId;
    }


}
