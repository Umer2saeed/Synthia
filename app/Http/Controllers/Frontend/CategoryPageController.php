<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\HasSeoMeta;
use App\Models\Category;
use App\Models\Tag;

class CategoryPageController extends Controller
{
    use HasSeoMeta;

    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
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
        | SEO for category page
        |----------------------------------------------------------------------
        | Title includes the category name so Google knows exactly
        | what this page lists.
        | Description uses the category's own description if set,
        | otherwise we generate one from the post count.
        */
        $seo = $this->buildSeo(
            title:       $category->name . ' — Articles',
            description: $category->description
            ?? 'Browse ' . $posts->total() . ' articles in the ' . $category->name . ' category on Synthia.',
            type:        'website',
        );

        return view('frontend.category', compact(
            'category', 'posts', 'categories', 'popularTags', 'seo'
        ));
    }
}
