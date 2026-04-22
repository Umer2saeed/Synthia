<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\CacheService;

class DashboardController extends Controller
{
    public function __construct(
        private CacheService $cache
    )
    {
    }

    public function index()
    {
        /*
        |----------------------------------------------------------------------
        | CACHED — shared stats (same for all admins, refreshes every 5 min)
        |----------------------------------------------------------------------
        | getDashboardStats() returns an array like:
        |   ['totalPosts' => 42, 'pendingComments' => 3, ...]
        |
        | extract() converts that array into local variables:
        |   $totalPosts = 42
        |   $pendingComments = 3
        |   ...
        |
        | This means the dashboard.blade.php view uses the same variable
        | names it already has — zero changes needed in the view.
        */
        $stats = $this->cache->getDashboardStats();
        extract($stats);

        /*
        |----------------------------------------------------------------------
        | NOT CACHED — recent activity (must always be fresh for admins)
        |----------------------------------------------------------------------
        | Admins need to see the very latest posts and comments immediately.
        | If we cached these, an admin who just published a post would not
        | see it in the recent list for up to 5 minutes — very confusing.
        */
        $recentPosts = Post::with(['category'])
            ->latest()
            ->limit(6)
            ->get();

        $recentComments = Comment::with(['user', 'post'])
            ->whereHas('post')
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::with(['roles'])
            ->latest()
            ->limit(5)
            ->get();

        /*
        |----------------------------------------------------------------------
        | NOT CACHED — personalized per logged-in admin
        |----------------------------------------------------------------------
        | These numbers are different for every user.
        | Caching them would cause Admin A to see Admin B's personal stats.
        */
        $myPostsCount = Post::where('user_id', auth()->id())->count();
        $myDraftsCount = Post::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->count();
        $myCommentsCount = Comment::where('user_id', auth()->id())->count();

        /*
        | compact() picks up ALL local variables including those created
        | by extract($stats) above.
        | The view receives the exact same variable names as before.
        | Your dashboard.blade.php requires absolutely zero changes.
        */
        return view('admin.dashboard', compact(
            'totalPosts', 'postsThisMonth', 'totalComments',
            'commentsThisWeek', 'totalUsers', 'usersThisMonth',
            'totalCategories', 'totalTags', 'pendingComments',
            'draftPosts', 'scheduledPosts', 'publishedPosts',
            'postsByStatus', 'topCategories', 'topTags',
            'recentPosts', 'recentComments', 'recentUsers',
            'myPostsCount', 'myDraftsCount', 'myCommentsCount',
        ));
    }
}

//
//namespace App\Http\Controllers\Admin;
//
//use App\Http\Controllers\Controller;
//use App\Models\Category;
//use App\Models\Comment;
//use App\Models\Post;
//use App\Models\Tag;
//use App\Models\User;
//
//class DashboardController extends Controller
//{
//    /*
//    |--------------------------------------------------------------------------
//    | index() — Gather all real stats and pass to dashboard view
//    |--------------------------------------------------------------------------
//    | We collect everything in one controller method to keep the view clean.
//    | Each query is commented so you understand exactly what it fetches.
//    */
//    public function index()
//    {
//        /*
//        |----------------------------------------------------------------------
//        | TOP STATS — the four summary cards
//        |----------------------------------------------------------------------
//        */
//
//        // Total posts ever created (including drafts + soft deleted)
//        $totalPosts = Post::withTrashed()->count();
//
//        // Posts created this calendar month
//        $postsThisMonth = Post::whereMonth('created_at', now()->month)
//            ->whereYear('created_at', now()->year)
//            ->count();
//
//        // Total approved comments
//        $totalComments = Comment::approved()->count();
//
//        // Comments created this week (last 7 days)
//        $commentsThisWeek = Comment::approved()
//            ->where('created_at', '>=', now()->subDays(7))
//            ->count();
//
//        // Total registered users
//        $totalUsers = User::count();
//
//        // Users registered this month
//        $usersThisMonth = User::whereMonth('created_at', now()->month)
//            ->whereYear('created_at', now()->year)
//            ->count();
//
//        // Total categories
//        $totalCategories = Category::count();
//
//        // Total tags
//        $totalTags = Tag::count();
//
//        /*
//        |----------------------------------------------------------------------
//        | PENDING STATS — things needing attention
//        |----------------------------------------------------------------------
//        */
//
//        // Comments awaiting approval
//        $pendingComments = Comment::pending()->count();
//
//        // Posts still in draft status (not soft deleted)
//        $draftPosts = Post::where('status', 'draft')->count();
//
//        // Posts scheduled for future publish
//        $scheduledPosts = Post::where('status', 'scheduled')->count();
//
//        // Published posts
//        $publishedPosts = Post::where('status', 'published')->count();
//
//        /*
//        |----------------------------------------------------------------------
//        | RECENT POSTS — last 6 posts with category relationship
//        |----------------------------------------------------------------------
//        | We use withTrashed(false) which is just the default — only live posts.
//        | with('category') eager loads to avoid N+1 queries in the view.
//        */
//        $recentPosts = Post::with('category')
//            ->latest()
//            ->limit(6)
//            ->get();
//
//        /*
//        |----------------------------------------------------------------------
//        | RECENT COMMENTS — last 5 comments with user + post
//        |----------------------------------------------------------------------
//        */
//        $recentComments = Comment::with(['user', 'post'])
//            ->whereHas('post') // exclude orphaned comments
//            ->latest()
//            ->limit(5)
//            ->get();
//
//        /*
//        |----------------------------------------------------------------------
//        | POSTS BY STATUS — for the mini breakdown chart
//        |----------------------------------------------------------------------
//        | groupBy() + selectRaw() runs a single SQL query:
//        | SELECT status, COUNT(*) as count FROM posts GROUP BY status
//        | We key the result by status so we can do $postsByStatus['draft'].
//        */
//        $postsByStatus = Post::selectRaw('status, count(*) as count')
//            ->groupBy('status')
//            ->pluck('count', 'status');
//
//        /*
//        |----------------------------------------------------------------------
//        | TOP CATEGORIES — by post count, limit 5
//        |----------------------------------------------------------------------
//        | withCount('posts') adds posts_count to each category.
//        */
//        $topCategories = Category::withCount('posts')
//            ->orderByDesc('posts_count')
//            ->limit(5)
//            ->get();
//
//        /*
//        |----------------------------------------------------------------------
//        | TOP TAGS — by post count, limit 8
//        |----------------------------------------------------------------------
//        */
//        $topTags = Tag::withCount('posts')
//            ->orderByDesc('posts_count')
//            ->limit(8)
//            ->get();
//
//        /*
//        |----------------------------------------------------------------------
//        | RECENT USERS — last 5 registered users
//        |----------------------------------------------------------------------
//        */
//        $recentUsers = User::with('roles')
//            ->latest()
//            ->limit(5)
//            ->get();
//
//        /*
//        |----------------------------------------------------------------------
//        | WELCOME CARD — personal stats for the logged-in user
//        |----------------------------------------------------------------------
//        */
//        $myPostsCount    = Post::where('user_id', auth()->id())->count();
//        $myDraftsCount   = Post::where('user_id', auth()->id())->where('status', 'draft')->count();
//        $myCommentsCount = Comment::where('user_id', auth()->id())->count();
//
//        /*
//        |----------------------------------------------------------------------
//        | Pass everything to the view
//        |----------------------------------------------------------------------
//        | compact() converts all local variables into an associative array.
//        | Each variable name becomes a key available in the Blade view.
//        */
//        return view('admin.dashboard', compact(
//        // Top stats
//            'totalPosts',
//            'postsThisMonth',
//            'totalComments',
//            'commentsThisWeek',
//            'totalUsers',
//            'usersThisMonth',
//            'totalCategories',
//            'totalTags',
//
//            // Pending attention
//            'pendingComments',
//            'draftPosts',
//            'scheduledPosts',
//            'publishedPosts',
//
//            // Lists
//            'recentPosts',
//            'recentComments',
//            'topCategories',
//            'topTags',
//            'recentUsers',
//
//            // Personal stats
//            'myPostsCount',
//            'myDraftsCount',
//            'myCommentsCount',
//        ));
//    }
//}
