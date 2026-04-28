<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const TTL_SHORT  = 300;
    const TTL_MEDIUM = 1800;
    const TTL_LONG   = 3600;

    /*
    |--------------------------------------------------------------------------
    | getSidebarCategories()
    |--------------------------------------------------------------------------
    | We convert the Eloquent Collection to a plain array before caching
    | using toArray(). On retrieval we wrap it back in a Collection using
    | collect(). This avoids the unserialize/incomplete object error.
    */
    public function getSidebarCategories(): Collection
    {
        $ids = Cache::remember(
            CacheKeys::SIDEBAR_CATEGORIES,
            self::TTL_LONG,
            fn() => \App\Models\Category::withCount([
                'posts' => fn($q) => $q->published()
            ])
                ->having('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->limit(10)
                ->pluck('id')
                ->toArray()
        );

        if (empty($ids)) return collect();

        return \App\Models\Category::withCount([
            'posts' => fn($q) => $q->published()
        ])
            ->whereIn('id', $ids)
            ->orderByDesc('posts_count')
            ->get();
    }

    public function getSidebarTags(): Collection
    {
        $ids = Cache::remember(
            CacheKeys::SIDEBAR_TAGS,
            self::TTL_LONG,
            fn() => \App\Models\Tag::withCount([
                'posts' => fn($q) => $q->published()
            ])
                ->having('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->limit(20)
                ->pluck('id')
                ->toArray()
        );

        if (empty($ids)) return collect();

        return \App\Models\Tag::withCount([
            'posts' => fn($q) => $q->published()
        ])
            ->whereIn('id', $ids)
            ->orderByDesc('posts_count')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | getFeaturedPosts()
    |--------------------------------------------------------------------------
    */
    public function getFeaturedPosts(int $limit = 3): Collection
    {
        $ids = Cache::remember(
            CacheKeys::HOME_FEATURED_POSTS,
            self::TTL_LONG,
            fn() => Post::published()
                ->where('is_featured', true)
                ->latest('published_at')
                ->limit($limit)
                ->pluck('id')
                ->toArray()
        );

        if (empty($ids)) {
            return collect();
        }

        return Post::with(['user', 'category', 'tags'])
            ->withCount([
                'claps',
                'comments',
                'bookmarks' => fn($q) => $q->where('user_id', auth()->id() ?? 0),
            ])
            ->whereIn('id', $ids)
            ->published()
            ->get()
            ->sortBy(fn($post) => array_search($post->id, $ids))
            ->values();
    }

    public function getLatestPosts(int $limit = 9): Collection
    {
        $ids = Cache::remember(
            CacheKeys::HOME_LATEST_POSTS,
            self::TTL_MEDIUM,
            fn() => Post::published()
                ->latest('published_at')
                ->limit($limit)
                ->pluck('id')
                ->toArray()
        );

        if (empty($ids)) {
            return collect();
        }

        /*
        | Add withCount here so the home page post cards do not
        | fire per-post queries for clap counts and comment counts.
        */
        return Post::with(['user', 'category', 'tags'])
            ->withCount([
                'claps',
                'comments',
                'bookmarks' => fn($q) => $q->where('user_id', auth()->id() ?? 0),
            ])
            ->whereIn('id', $ids)
            ->published()
            ->get()
            ->sortBy(fn($post) => array_search($post->id, $ids))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | getPostBySlug()
    |--------------------------------------------------------------------------
    | For a single post we cache the array representation and rebuild
    | a plain collection with one item — the controller checks for null.
    |
    | WHY not rebuild as a Post model?
    | Rebuilding Eloquent models from arrays requires hydration logic.
    | The simpler approach is to cache the array and let the controller
    | query fresh when needed for a single post (still fast with indexes).
    |
    | For the single post page we skip caching entirely and query directly.
    | Single post pages are already fast due to MySQL indexes on slug.
    | The benefit of caching a single post is small compared to the
    | complexity of rebuilding model relationships from arrays.
    |--------------------------------------------------------------------------
    */
    public function getPostBySlug(string $slug): ?Post
    {
        /*
        | For single posts we query directly without caching.
        | MySQL uses the slug index so this is one fast query.
        | Caching Eloquent models with nested relationships
        | causes the incomplete object error you are seeing.
        */
        return Post::with(['user', 'category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | getRelatedPosts()
    |--------------------------------------------------------------------------
    */
    public function getRelatedPosts(Post $post, int $limit = 3): Collection
    {
        $ids = Cache::remember(
            CacheKeys::postRelated($post->id),
            self::TTL_MEDIUM,
            fn() => Post::with(['user', 'category'])
                ->published()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->latest('published_at')
                ->limit($limit)
                ->pluck('id')
                ->toArray()
        );

        if (empty($ids)) {
            return collect();
        }

        return Post::with(['user', 'category'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn($p) => array_search($p->id, $ids))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | getDashboardStats()
    |--------------------------------------------------------------------------
    | Dashboard stats are scalar values and simple arrays — no Eloquent models.
    | These are safe to cache directly because they contain only:
    |   - integers (counts)
    |   - plain Laravel Collection from pluck() (not Eloquent Collection)
    |   - plain arrays from get()->toArray() for top categories/tags
    */
    public function getDashboardStats(): array
    {
        return Cache::remember(
            CacheKeys::DASHBOARD_STATS,
            self::TTL_SHORT,
            function () {
                /*
                | All values here must be plain PHP scalars or plain arrays.
                | NO Eloquent models, NO Eloquent Collections, NO Paginators.
                |
                | topCategories and topTags use ->get()->toArray() to convert
                | Eloquent Collection → plain PHP array before caching.
                | The dashboard blade iterates over these arrays using @foreach
                | and accesses values with $item['name'] not $item->name.
                |
                | postsByStatus uses ->pluck() which returns a plain
                | Illuminate\Support\Collection of scalars — safe to cache.
                */
                return [
                    'totalPosts'       => Post::withTrashed()->count(),
                    'postsThisMonth'   => Post::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'totalComments'    => Comment::approved()->count(),
                    'commentsThisWeek' => Comment::approved()
                        ->where('created_at', '>=', now()->subDays(7))
                        ->count(),
                    'totalUsers'       => User::count(),
                    'usersThisMonth'   => User::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'totalCategories'  => Category::count(),
                    'totalTags'        => Tag::count(),
                    'pendingComments'  => Comment::pending()->count(),
                    'draftPosts'       => Post::where('status', 'draft')->count(),
                    'scheduledPosts'   => Post::where('status', 'scheduled')->count(),
                    'publishedPosts'   => Post::where('status', 'published')->count(),
                    'postsByStatus'    => Post::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray(), // convert to plain array
                    'topCategories'    => Category::withCount('posts')
                        ->orderByDesc('posts_count')
                        ->limit(5)
                        ->get()
                        ->toArray(), // plain array
                    'topTags'          => Tag::withCount('posts')
                        ->orderByDesc('posts_count')
                        ->limit(8)
                        ->get()
                        ->toArray(), // plain array
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cache invalidation methods — unchanged from before
    |--------------------------------------------------------------------------
    */
    public function clearPostCaches(Post $post): void
    {
        Cache::forget(CacheKeys::postBySlug($post->slug));

        if ($post->wasChanged('slug') && $post->getOriginal('slug')) {
            Cache::forget(CacheKeys::postBySlug($post->getOriginal('slug')));
        }

        Cache::forget(CacheKeys::postRelated($post->id));
        Cache::forget(CacheKeys::HOME_FEATURED_POSTS);
        Cache::forget(CacheKeys::HOME_LATEST_POSTS);
        Cache::forget(CacheKeys::DASHBOARD_STATS);

        for ($i = 1; $i <= 20; $i++) {
            Cache::forget(CacheKeys::blogPage($i));
        }
    }

    public function clearSidebarCaches(): void
    {
        Cache::forget(CacheKeys::SIDEBAR_CATEGORIES);
        Cache::forget(CacheKeys::SIDEBAR_TAGS);
    }

    public function clearDashboardCache(): void
    {
        Cache::forget(CacheKeys::DASHBOARD_STATS);
    }

    public function clearAll(): void
    {
        Cache::forget(CacheKeys::SIDEBAR_CATEGORIES);
        Cache::forget(CacheKeys::SIDEBAR_TAGS);
        Cache::forget(CacheKeys::HOME_FEATURED_POSTS);
        Cache::forget(CacheKeys::HOME_LATEST_POSTS);
        Cache::forget(CacheKeys::DASHBOARD_STATS);

        for ($i = 1; $i <= 20; $i++) {
            Cache::forget(CacheKeys::blogPage($i));
        }
    }
}
