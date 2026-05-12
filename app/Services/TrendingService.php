<?php

namespace App\Services;

use App\Models\Clap;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostTrendingScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrendingService
{
    const CACHE_KEY    = 'trending.posts';
    const CACHE_TTL    = 60 * 60 * 6; // 6 hours in seconds
    const TOP_COUNT    = 10;           // how many posts are "trending"
    const DAYS_WINDOW  = 7;            // only posts from last N days

    /*
    | recalculate() — Run the trending algorithm and update the DB.
    | Called by the scheduled Artisan command every 6 hours.
    */
    public function recalculate(): int
    {
        $since = now()->subDays(self::DAYS_WINDOW);

        $posts = Post::published()
            ->where('published_at', '>=', $since)
            ->with(['trendingScore'])
            ->withCount(['comments' => fn($q) => $q->approved()])
            ->get();

        $clapsMap = $this->buildClapsMap($posts->pluck('id')->toArray());

        DB::transaction(function () use ($posts, $clapsMap) {
            // Clear old scores
            PostTrendingScore::whereNotIn(
                'post_id',
                $posts->pluck('id')
            )->delete();

            foreach ($posts as $post) {
                $views    = $post->views ?? 0;
                $claps    = $clapsMap[$post->id] ?? 0;
                $comments = $post->comments_count ?? 0;

                $score = ($views * 2) + ($claps * 3) + ($comments * 5);

                PostTrendingScore::updateOrCreate(
                    ['post_id' => $post->id],
                    [
                        'score'             => $score,
                        'views_snapshot'    => $views,
                        'claps_snapshot'    => $claps,
                        'comments_snapshot' => $comments,
                        'calculated_at'     => now(),
                    ]
                );
            }
        });

        // Clear cached results so next request gets fresh data
        Cache::forget(self::CACHE_KEY);

        return $posts->count();
    }

    /*
    | getTrending() — Return top N trending posts, cached.
    */
    public function getTrending(int $limit = self::TOP_COUNT): Collection
    {
        $ids = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($limit) {
            return PostTrendingScore::orderByDesc('score')
                ->limit($limit)
                ->pluck('post_id')
                ->toArray();
        });

        if (empty($ids)) {
            return collect();
        }

        return Post::with(['user', 'category'])
            ->withCount(['claps', 'comments'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn($p) => array_search($p->id, $ids))
            ->values();
    }

    /*
    | getTrendingIds() — Return just the IDs of trending posts.
    | Used for badge checking on post cards without extra queries.
    */
    public function getTrendingIds(): array
    {
        return Cache::remember(self::CACHE_KEY . '.ids', self::CACHE_TTL, function () {
            return PostTrendingScore::orderByDesc('score')
                ->limit(self::TOP_COUNT)
                ->pluck('post_id')
                ->toArray();
        });
    }

    private function buildClapsMap(array $postIds): array
    {
        return Clap::whereIn('post_id', $postIds)
            ->groupBy('post_id')
            ->selectRaw('post_id, SUM(`count`) as total')
            ->pluck('total', 'post_id')
            ->map(fn($v) => (int) $v)
            ->toArray();
    }
}
