<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Clap;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Post;
use App\Models\ReadingHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthorAnalyticsController extends Controller
{
    public function index()
    {
        $author = auth()->user();

        /*
        | Cache only the computed scalar arrays — never Eloquent models.
        | TTL: 30 minutes. Authors need reasonably fresh data but
        | we do not want heavy queries on every page load.
        */
        $data = Cache::remember(
            'author.analytics.' . $author->id,
            now()->addMinutes(30),
            fn() => $this->buildAnalyticsData($author)
        );

        return view('frontend.author-analytics', array_merge(
            ['author' => $author],
            $data
        ));
    }

    private function buildAnalyticsData($author): array
    {
        $postIds = Post::published()
            ->where('user_id', $author->id)
            ->pluck('id')
            ->toArray();

        return [
            'totalStats'     => $this->totalStats($author->id, $postIds),
            'viewsChart'     => $this->viewsOverTime($author->id),
            'clapsChart'     => $this->clapsOverTime($postIds),
            'commentsChart'  => $this->commentsOverTime($postIds),
            'followerChart'  => $this->followerGrowth($author->id),
            'topByViews'     => $this->topPosts($postIds, 'views'),
            'topByClaps'     => $this->topPostsByClaps($postIds),
            'topByComments'  => $this->topPostsByComments($postIds),
            'referrers'      => $this->referrerSources($postIds),
        ];
    }

    /*
    | Four summary numbers shown at the top.
    */
    private function totalStats(int $authorId, array $postIds): array
    {
        return [
            'total_views'     => Post::where('user_id', $authorId)->sum('views'),
            'total_claps'     => Clap::whereIn('post_id', $postIds)->sum('count'),
            'total_comments'  => Comment::whereIn('post_id', $postIds)->approved()->count(),
            'total_followers' => Follow::where('following_id', $authorId)->count(),
            'total_posts'     => count($postIds),
        ];
    }

    /*
    | Daily view counts for the last 30 days.
    | Source: reading_history table (tracks individual post visits).
    */
    private function viewsOverTime(int $authorId): array
    {
        $rows = ReadingHistory::whereHas(
            'post',
            fn($q) => $q->where('user_id', $authorId)
        )
            ->where('read_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(read_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $this->fillDateRange($rows, 30);
    }

    /*
    | Daily clap counts for the last 30 days.
    */
    private function clapsOverTime(array $postIds): array
    {
        if (empty($postIds)) {
            return $this->fillDateRange([], 30);
        }

        $rows = Clap::whereIn('post_id', $postIds)
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(created_at) as date, SUM(`count`) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $this->fillDateRange($rows, 30);
    }

    /*
    | Daily comment counts for the last 30 days.
    */
    private function commentsOverTime(array $postIds): array
    {
        if (empty($postIds)) {
            return $this->fillDateRange([], 30);
        }

        $rows = Comment::whereIn('post_id', $postIds)
            ->approved()
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $this->fillDateRange($rows, 30);
    }

    /*
    | Weekly follower counts for the last 8 weeks.
    */
    private function followerGrowth(int $authorId): array
    {
        $rows = Follow::where('following_id', $authorId)
            ->where('created_at', '>=', now()->subWeeks(8))
            ->selectRaw('DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY)) as week, COUNT(*) as count')
            ->groupBy('week')
            ->orderBy('week')
            ->pluck('count', 'week')
            ->toArray();

        return $this->fillWeekRange($rows, 8);
    }

    /*
    | Top 5 posts by a column on the posts table (e.g. views).
    */
    private function topPosts(array $postIds, string $column): array
    {
        if (empty($postIds)) return [];

        return Post::whereIn('id', $postIds)
            ->orderByDesc($column)
            ->limit(5)
            ->get(['id', 'title', 'slug', $column])
            ->map(fn($p) => [
                'id'    => $p->id,
                'title' => $p->title,
                'slug'  => $p->slug,
                'value' => (int) $p->$column,
            ])
            ->toArray();
    }

    /*
    | Top 5 posts by total claps received.
    */
    private function topPostsByClaps(array $postIds): array
    {
        if (empty($postIds)) return [];

        return Clap::whereIn('post_id', $postIds)
            ->selectRaw('post_id, SUM(`count`) as total')
            ->groupBy('post_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('post:id,title,slug')
            ->get()
            ->map(fn($c) => [
                'id'    => $c->post_id,
                'title' => $c->post->title ?? '—',
                'slug'  => $c->post->slug ?? '',
                'value' => (int) $c->total,
            ])
            ->toArray();
    }

    /*
    | Top 5 posts by approved comment count.
    */
    private function topPostsByComments(array $postIds): array
    {
        if (empty($postIds)) return [];

        return Comment::whereIn('post_id', $postIds)
            ->approved()
            ->selectRaw('post_id, COUNT(*) as total')
            ->groupBy('post_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('post:id,title,slug')
            ->get()
            ->map(fn($c) => [
                'id'    => $c->post_id,
                'title' => $c->post->title ?? '—',
                'slug'  => $c->post->slug ?? '',
                'value' => (int) $c->total,
            ])
            ->toArray();
    }

    /*
    | Traffic sources — placeholder using reading_history.
    | In production you would parse HTTP Referer headers stored per visit.
    | We simulate this with categorized source labels.
    */
    private function referrerSources(array $postIds): array
    {
        if (empty($postIds)) return [];

        /*
        | Since we do not store referrers yet, we return a breakdown
        | of views by category as a proxy for "content topics driving traffic".
        | This gives authors useful data without requiring referrer tracking.
        */
        return Post::whereIn('id', $postIds)
            ->with('category:id,name')
            ->get(['id', 'title', 'views', 'category_id'])
            ->groupBy(fn($p) => $p->category->name ?? 'Uncategorized')
            ->map(fn($posts) => $posts->sum('views'))
            ->sortByDesc(fn($v) => $v)
            ->take(6)
            ->map(fn($views, $category) => [
                'source' => $category,
                'views'  => (int) $views,
            ])
            ->values()
            ->toArray();
    }

    /*
    | Fill a date-indexed array with zero values for missing days.
    | Returns ['labels' => [...], 'data' => [...]] for Chart.js.
    */
    private function fillDateRange(array $rows, int $days): array
    {
        $labels = [];
        $data   = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[]   = (int) ($rows[$date] ?? 0);
        }

        return compact('labels', 'data');
    }

    /*
    | Fill a week-indexed array with zero values for missing weeks.
    */
    private function fillWeekRange(array $rows, int $weeks): array
    {
        $labels = [];
        $data   = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $monday   = now()->subWeeks($i)->startOfWeek()->format('Y-m-d');
            $labels[] = 'Week of ' . now()->subWeeks($i)->startOfWeek()->format('M d');
            $data[]   = (int) ($rows[$monday] ?? 0);
        }

        return compact('labels', 'data');
    }
}
