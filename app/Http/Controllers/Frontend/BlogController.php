<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCommentRequest;
use App\Models\Clap;
use App\Services\PostSearchService;
use App\Traits\HasSeoMeta;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Bookmark;

class BlogController extends Controller
{
    use HasSeoMeta;

    /*
    |--------------------------------------------------------------------------
    | Inject PostSearchService via constructor
    |--------------------------------------------------------------------------
    | Laravel's service container automatically creates and injects
    | the PostSearchService when BlogController is instantiated.
    |
    | This is called Dependency Injection — we declare what we need
    | in the constructor and Laravel provides it automatically.
    | We never need to call: new PostSearchService()
    */
    public function __construct(
        private PostSearchService $searchService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | index() — Blog listing with full text search
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $searchQuery = (string) $request->input('search', '');
        $category    = (string) $request->input('category', '');

        /*
        |----------------------------------------------------------------------
        | Use PostSearchService to handle the search
        |----------------------------------------------------------------------
        | The service decides internally whether to use:
        |   - Full text search (normal queries)
        |   - LIKE search (very short queries)
        |   - Latest posts (no query)
        |
        | We pass the search query and filters as arguments.
        | The service returns a paginated result collection.
        */
        $posts = $this->searchService->search(
            query: $searchQuery,
            perPage: 12,
            filters: ['category' => $category],
        );

        $categories = Category::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->get();
        $popularTags = Tag::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(15)->get();

        /*
        |----------------------------------------------------------------------
        | SEO title reflects the search query
        |----------------------------------------------------------------------
        */
        $seoTitle = $request->filled('search')
            ? 'Results for "' . $searchQuery . '"'
            : 'Blog — All Articles';

        $seo = $this->buildSeo(
            title: $seoTitle,
            description: 'Browse all articles, tutorials, and stories published on Synthia.',
            type: 'website',
        );

        return view('frontend.blog', compact(
            'posts',
            'categories',
            'popularTags',
            'searchQuery', // pass to view for highlighting
            'seo',
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | show() — Single post page
    |--------------------------------------------------------------------------
    */
    public function show(string $slug)
    {
        $post = Post::with(['user', 'category', 'tags'])
            ->published()->where('slug', $slug)->firstOrFail();

        $comments = $post->comments()->approved()->with('user')->latest()->get();

        $relatedPosts = Post::with(['user', 'category'])
            ->published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')->limit(3)->get();

        $categories  = Category::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(8)->get();
        $popularTags = Tag::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(15)->get();

        /*
    |----------------------------------------------------------------------
    | Clap data for this post
    |----------------------------------------------------------------------
    | totalClaps() → sum of ALL users' clap counts on this post
    | userClaps()  → how many times the CURRENT user has clapped
    |                returns 0 if not logged in
    | maxClaps     → the maximum allowed (50) — passed to JS
    |                so the button knows when to disable itself
    */
        $totalClaps = $post->totalClaps();
        $userClaps  = $post->userClaps();
        $maxClaps   = Clap::MAX_CLAPS_PER_USER;

        /*
    |----------------------------------------------------------------------
    | Bookmark state for this post
    |----------------------------------------------------------------------
    | isBookmarked → true if the current user has bookmarked this post
    |                false if not logged in or not bookmarked
    |
    | We pass this to the view so the bookmark button shows the
    | correct state (filled vs empty) when the page loads.
    | Without this, the button would always show as "not bookmarked"
    | even if the user already bookmarked the post before.
    */
        $isBookmarked = $post->isBookmarkedBy(auth()->user());

        /*
        |----------------------------------------------------------------------
        | SEO for single post page
        |----------------------------------------------------------------------
        | Type is 'article' — this triggers the article:author and
        | article:published_time Open Graph tags in the component.
        |
        | Image: use the post's actual cover image so sharing this post
        | on WhatsApp/Twitter shows the real cover image.
        |
        | Description: prefer ai_summary if set (written for humans),
        | otherwise extract plain text from content and limit it.
        */
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

        return view('frontend.post', compact(
            'post',
            'comments',
            'relatedPosts',
            'categories',
            'popularTags',
            'seo',
            'totalClaps',
            'userClaps',
            'maxClaps',
            'isBookmarked',
        ));
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
