<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\HasSeoMeta;
use App\Models\Tag;
use App\Models\Category;

class TagPageController extends Controller
{
    use HasSeoMeta;

    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->with(['user', 'category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $categories  = Category::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->get();
        $popularTags = Tag::withCount(['posts' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')->limit(15)->get();

        /*
        |----------------------------------------------------------------------
        | SEO for tag page
        |----------------------------------------------------------------------
        | Description tells Google this is a collection of posts
        | tagged with a specific topic.
        */
        $seo = $this->buildSeo(
            title:       '#' . $tag->name . ' — Tagged Articles',
            description: 'Browse ' . $posts->total() . ' articles tagged with "' . $tag->name . '" on Synthia.',
            type:        'website',
        );

        return view('frontend.tag', compact(
            'tag', 'posts', 'categories', 'popularTags', 'seo'
        ));
    }
}
