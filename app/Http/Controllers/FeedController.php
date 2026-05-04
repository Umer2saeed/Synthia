<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — Main RSS feed
    |--------------------------------------------------------------------------
    | WHY we do NOT cache the full Post collection here:
    | Caching Eloquent model collections in file cache can cause
    | deserialization issues — models come back as plain objects
    | without accessor methods, causing "Attempt to read property on string".
    |
    | Instead we cache only the IDs (safe plain integers) and re-fetch
    | fresh models on each request. The query is fast because:
    |   1. We only fetch 20 posts
    |   2. whereIn() on primary key uses the index
    |   3. RSS feeds are not hit frequently enough to need heavy caching
    */
    public function index(): Response
    {
        $posts = $this->getPosts();

        return response()
            ->view('feed.rss', [
                'posts'       => $posts,
                'title'       => config('app.name') . ' — Blog',
                'description' => 'Latest articles from ' . config('app.name'),
                'feedUrl'     => route('feed.index'),
                'siteUrl'     => url('/blog'),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /*
    |--------------------------------------------------------------------------
    | category() — Category-specific RSS feed
    |--------------------------------------------------------------------------
    */
    public function category(string $slug): Response
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $this->getPostsByCategory($category->id);

        return response()
            ->view('feed.rss', [
                'posts'       => $posts,
                'title'       => config('app.name') . ' — ' . $category->name,
                'description' => 'Latest articles about ' . $category->name . ' from ' . config('app.name'),
                'feedUrl'     => route('feed.category', $category->slug),
                'siteUrl'     => route('blog.category', $category->slug),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /*
    |--------------------------------------------------------------------------
    | getPosts() — Fetch the 20 most recent published posts
    |--------------------------------------------------------------------------
    | We cache only the IDs for 1 hour.
    | Then re-fetch full Post models fresh from the database.
    | This avoids the Eloquent deserialization problem entirely.
    */
    private function getPosts()
    {
        $ids = cache()->remember('rss.feed.main.ids', now()->addHour(), function () {
            return Post::published()
                ->latest('published_at')
                ->limit(20)
                ->pluck('id')
                ->toArray();
        });

        if (empty($ids)) {
            return collect();
        }

        /*
        | Re-fetch fresh Eloquent models using the cached IDs.
        | Models fetched fresh are guaranteed to have all accessors
        | and relationships working correctly.
        */
        return Post::with(['user', 'category'])
            ->whereIn('id', $ids)
            ->orderByDesc('published_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | getPostsByCategory() — Fetch posts for a specific category
    |--------------------------------------------------------------------------
    */
    private function getPostsByCategory(int $categoryId)
    {
        $ids = cache()->remember(
            'rss.feed.category.ids.' . $categoryId,
            now()->addHour(),
            function () use ($categoryId) {
                return Post::published()
                    ->where('category_id', $categoryId)
                    ->latest('published_at')
                    ->limit(20)
                    ->pluck('id')
                    ->toArray();
            }
        );

        if (empty($ids)) {
            return collect();
        }

        return Post::with(['user', 'category'])
            ->whereIn('id', $ids)
            ->orderByDesc('published_at')
            ->get();
    }
}
