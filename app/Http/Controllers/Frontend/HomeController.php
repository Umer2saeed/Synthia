<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Services\SchemaService;
use App\Traits\HasSeoMeta;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class HomeController extends Controller
{
    use HasSeoMeta;

    public function __construct(
        private CacheService $cache
    ) {}

    public function index()
    {
        $featuredPosts = $this->cache->getFeaturedPosts(3);
        $latestPosts   = $this->cache->getLatestPosts(9);
        $categories    = $this->cache->getSidebarCategories();
        $popularTags   = $this->cache->getSidebarTags();
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

        $schemaWebsite = app(SchemaService::class)->website();


        return view('frontend.home', compact(
            'featuredPosts', 'latestPosts', 'categories', 'popularTags', 'seo', 'schemaWebsite'
        ));
    }
}
