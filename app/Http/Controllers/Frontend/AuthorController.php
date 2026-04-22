<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Traits\HasSeoMeta;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    use HasSeoMeta;

    public function __construct(
        private CacheService $cache
    )
    {
    }

    public function show(string $username)
    {
        $author = User::where('username', $username)
            ->orWhere('id', $username)
            ->firstOrFail();
        $author->load('roles');

        $author->loadCount([
            'posts'     => fn($q) => $q->published(),
            'followers',
            'following',
        ]);

        $posts = $author->posts()
            ->published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->paginate(9);

//        $categories  = Category::withCount(['posts' => fn($q) => $q->published()])
//            ->orderByDesc('posts_count')
//            ->limit(8)
//            ->get();
//
//        $popularTags = Tag::withCount(['posts' => fn($q) => $q->published()])
//            ->orderByDesc('posts_count')
//            ->limit(15)
//            ->get();

        $categories  = $this->cache->getSidebarCategories();
        $popularTags = $this->cache->getSidebarTags();
        /*
        |----------------------------------------------------------------------
        | Check if the logged-in user follows this author
        |----------------------------------------------------------------------
        | isFollowing() queries the follows table.
        | Returns false for guests — they cannot follow anyone.
        |
        | We pass this to the view so the Follow button renders
        | in the correct initial state (Follow or Following).
        */
        $isFollowing = auth()->check()
            ? auth()->user()->isFollowing($author)
            : false;

        $seo = $this->buildSeo(
            title:       $author->display_name . ' — Author',
            description: $author->bio
            ?? $author->display_name . ' has published ' .
            $author->posts_count . ' ' .
            Str::plural('article', $author->posts_count) . ' on Synthia.',
            image:       $author->avatar
                ? asset('storage/' . $author->avatar)
                : asset('images/og-default.jpg'),
            type:        'website',
        );

        return view('frontend.author', compact(
            'author',
            'posts',
            'categories',
            'popularTags',
            'isFollowing',
            'seo',
        ));
    }
}
