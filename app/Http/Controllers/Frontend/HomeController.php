<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\HasSeoMeta;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class HomeController extends Controller
{
    use HasSeoMeta;

    public function index()
    {
        $featuredPosts = Post::with(['user', 'category'])
            ->published()->featured()
            ->latest('published_at')->limit(3)->get();

        $latestPosts = Post::with(['user', 'category', 'tags'])
            ->published()
            ->latest('published_at')->limit(9)->get();

        $categories = Category::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(8)->get();

        $popularTags = Tag::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(15)->get();

        /*
        |----------------------------------------------------------------------
        | SEO for the home page
        |----------------------------------------------------------------------
        | Type 'website' — this is not an article, it is the site home page.
        | Description tells visitors and Google what Synthia is about.
        */
        $seo = $this->buildSeo(
            title:       config('app.name') . ' — Ideas Worth Reading',
            description: 'Explore stories, insights, and tutorials from writers who care about quality. Discover the latest posts on Synthia.',
            type:        'website',
        );

        return view('frontend.home', compact(
            'featuredPosts', 'latestPosts', 'categories', 'popularTags', 'seo'
        ));
    }
}
