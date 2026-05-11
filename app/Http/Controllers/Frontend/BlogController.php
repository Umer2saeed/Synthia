<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCommentRequest;
use App\Jobs\SendNewCommentNotificationJob;
use App\Models\Clap;
use App\Services\PostSearchService;
use App\Services\PostViewService;
use App\Services\SanitizationService;
use App\Traits\HasSeoMeta;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Bookmark;
use App\Services\CacheService;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    use HasSeoMeta;

    public function __construct(
        private CacheService     $cache,
        private PostSearchService $searchService
    ) {}


    public function index(Request $request)
    {
        $sanitizer   = app(SanitizationService::class);
        $searchQuery = $sanitizer->cleanSearch((string) $request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 12;

        if (empty($searchQuery)) {
            $cacheKey = \App\Support\CacheKeys::blogPage($page);

            $cached = \Illuminate\Support\Facades\Cache::remember(
                $cacheKey,
                \App\Services\CacheService::TTL_MEDIUM,
                function () use ($page, $perPage) {
                    /*
                    | Run a paginated query but only pluck the IDs.
                    | This is much lighter than loading full models with relationships.
                    | We also capture the total count for rebuilding the paginator.
                    */
                    $paginator = Post::published()
                        ->latest('published_at')
                        ->paginate($perPage, ['id'], 'page', $page);

                    return [
                        'ids'   => $paginator->pluck('id')->toArray(),
                        'total' => $paginator->total(),
                    ];
                }
            );

            /*
            |----------------------------------------------------------------------
            | Re-fetch fresh Post models using the cached IDs.
            | whereIn('id', $ids) uses the PRIMARY KEY index — extremely fast.
            | We preserve the original order using orderByRaw with FIELD().
            |----------------------------------------------------------------------
            */
            $ids = $cached['ids'];

            if (!empty($ids)) {
//                $idList = implode(',', $ids);
                /*
                | Fetch posts using IN() — MySQL uses the primary key index.
                | We do NOT use orderByRaw FIELD() here because:
                |   1. FIELD() has overhead for large ID lists
                |   2. We restore the original order in PHP using sortBy
                |      which is O(n log n) on a small collection (12 items max)
                |   3. PHP sorting of 12 items is faster than MySQL FIELD()
                */
                $items = Post::with(['user', 'category', 'tags'])
                    ->withCount([
                        'claps',
                        'comments',
                        'bookmarks' => fn($q) => $q->where(
                            'user_id', auth()->id() ?? 0
                        ),
                    ])
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn($post) => array_search($post->id, $ids))
                    ->values();
            } else {
                $items = collect();
            }

            /*
            |----------------------------------------------------------------------
            | Manually build a LengthAwarePaginator from our cached data.
            |
            | LengthAwarePaginator constructor:
            |   $items    → the actual items for this page (our Post models)
            |   $total    → total number of records across all pages
            |   $perPage  → how many items per page
            |   $page     → current page number
            |   $options  → path and query string for pagination links
            |----------------------------------------------------------------------
            */
            $posts = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $cached['total'],
                $perPage,
                $page,
                [
                    'path'  => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );

        } else {
            // Search results bypass cache completely
            $posts = $this->searchService->search($searchQuery, $perPage);
        }

        $categories  = $this->cache->getSidebarCategories();
        $popularTags = $this->cache->getSidebarTags();

        $seoTitle = $request->filled('search')
            ? "Results for \"{$searchQuery}\""
            : 'Blog — All Articles';

        $seo = $this->buildSeo(
            title:       $seoTitle,
            description: 'Browse all articles on Synthia.',
            type:        'website',
        );

        return view('frontend.blog', compact(
            'posts', 'categories', 'popularTags', 'searchQuery', 'seo'
        ));
    }

    public function show(string $slug, Request $request)
    {
        $post = $this->cache->getPostBySlug($slug);

        if (!$post) {
            abort(404);
        }

        $viewService = app(PostViewService::class);

        $isOwnPost = auth()->check() && auth()->id() === $post->user_id;
        $isStaff   = auth()->check() && auth()->user()->hasAnyRole(['admin', 'editor']);

        if (!$isOwnPost && !$isStaff) {
            $viewService->record($post, $request);
        }

        $clapData = \App\Models\Clap::where('post_id', $post->id)
            ->selectRaw('COALESCE(SUM(`count`), 0) as total_claps,COALESCE(SUM(CASE WHEN user_id = ? THEN `count` ELSE 0 END), 0) as user_claps', [auth()->id() ?? 0])
            ->first();
        $totalClaps = (int) ($clapData->total_claps ?? 0);
        $userClaps  = (int) ($clapData->user_claps ?? 0);
        $maxClaps   = \App\Models\Clap::MAX_CLAPS_PER_USER;

        /*
        |----------------------------------------------------------------------
        | Load comments with user in one query
        |----------------------------------------------------------------------
        | with('user') is already there — confirming it is correct.
        */
        $comments = $post->comments()
            ->approved()
            ->with(['user'])
            ->latest()
            ->get();


        /*
        |----------------------------------------------------------------------
        | Check bookmark status efficiently
        |----------------------------------------------------------------------
        | BEFORE: isBookmarkedBy() runs EXISTS query
        | AFTER:  same EXISTS query but only when user is logged in
        |         guests skip this query entirely
        */
        $isBookmarked = auth()->check()
            ? \App\Models\Bookmark::where('user_id', auth()->id())
                ->where('post_id', $post->id)
                ->exists()
            : false;

        $relatedPosts = $this->cache->getRelatedPosts($post);
        $categories  = $this->cache->getSidebarCategories();
        $popularTags = $this->cache->getSidebarTags();

        $seo = $this->buildSeo(
            title:       $post->title,
            description: $post->ai_summary ?? \Str::limit(strip_tags($post->content), 155),
            image:       $post->cover_image
                ? asset('storage/' . $post->cover_image)
                : asset('images/og-default.jpg'),
            type:        'article',
            author:      $post->user->name,
            publishedAt: $post->published_at?->toIso8601String(),
        );

        /*
   | Load reaction counts and current user's reaction in one efficient block.
   | We use a single query for counts and one EXISTS check for user reaction.
   */
        $reactionCounts  = $post->getReactionCounts();
        $userReaction    = auth()->check()
            ? \App\Models\Reaction::where('user_id', auth()->id())
                ->where('post_id', $post->id)
                ->value('type') // returns the type string or null
            : null;



        /*
    | Load series context for prev/next navigation.
    | We load the first series this post belongs to.
    | If a post is in multiple series, we show only the first.
    */
        $postSeries     = null;
        $seriesAllPosts = collect();
        $seriesPrev     = null;
        $seriesNext     = null;

        $firstSeries = $post->series()->first();

        if ($firstSeries) {
            $postSeries     = $firstSeries;
            $seriesAllPosts = $firstSeries->publishedPosts()->get();

            $currentIndex = $seriesAllPosts->search(
                fn($p) => $p->id === $post->id
            );

            if ($currentIndex !== false) {
                $seriesPrev = $currentIndex > 0
                    ? $seriesAllPosts[$currentIndex - 1]
                    : null;

                $seriesNext = $currentIndex < $seriesAllPosts->count() - 1
                    ? $seriesAllPosts[$currentIndex + 1]
                    : null;
            }
        }

        return view('frontend.post', compact(
            'post', 'comments', 'relatedPosts', 'categories',
            'popularTags', 'totalClaps', 'userClaps', 'maxClaps',
            'isBookmarked', 'seo',
            'reactionCounts', 'userReaction',
            'postSeries', 'seriesAllPosts', 'seriesPrev', 'seriesNext',
        ));
    }

    public function category(string $slug)
    {
        $category = \App\Models\Category::where('slug', $slug)->firstOrFail();

        $page    = (int) request()->input('page', 1);
        $perPage = 12;

        $cacheKey = 'category_posts_' . $slug . '_page_' . $page;

        $cached = \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            \App\Services\CacheService::TTL_MEDIUM,
            function () use ($category, $page, $perPage) {
                $paginator = Post::published()
                    ->where('category_id', $category->id)
                    ->latest('published_at')
                    ->paginate($perPage, ['id'], 'page', $page);

                return [
                    'ids'   => $paginator->pluck('id')->toArray(),
                    'total' => $paginator->total(),
                ];
            }
        );

        $ids = $cached['ids'];

        if (!empty($ids)) {
            $items = Post::with(['user', 'category', 'tags'])
                ->withCount([
                    'claps',
                    'comments',
                    'bookmarks' => fn($q) => $q->where('user_id', auth()->id() ?? 0),
                ])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn($post) => array_search($post->id, $ids))
                ->values();
        } else {
            $items = collect();
        }

        $posts = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $cached['total'],
            $perPage,
            $page,
            [
                'path'  => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        $categories  = $this->cache->getSidebarCategories();
        $popularTags = $this->cache->getSidebarTags();

        $seo = $this->buildSeo(
            title:       $category->name . ' — Articles',
            description: $category->description ?? 'Browse all articles in ' . $category->name,
            type:        'website',
        );

        return view('frontend.blog', compact(
            'posts', 'categories', 'popularTags', 'seo', 'category'
        ))->with('currentCategory', $category);
    }


    /*
    | storeComment() and destroyComment() unchanged — no SEO needed
    */
    public function storeComment(StoreCommentRequest $request): JsonResponse
    {
        $post = Post::published()->findOrFail($request->validated()['post_id']);

        $comment = Comment::create([
            'user_id'     => auth()->id(),
            'post_id'     => $post->id,
            'content'     => $request->validated()['content'],
            'is_approved' => true,
        ]);

        $comment->load('user');

        // After comment is created, clear dashboard cache
        Cache::forget('reader.dashboard.stats.' . auth()->id());
        SendNewCommentNotificationJob::dispatch($comment);

        $html = view('frontend.partials._comment', compact('comment'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
            'count'   => $post->comments()->approved()->count(),
            'message' => 'Comment posted successfully.',
        ]);
    }

    public function destroyComment(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own comments.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted.',
        ]);
    }
}
