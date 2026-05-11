<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Series;

class SeriesController extends Controller
{
    /*
    | Public series listing page — /series
    */
    public function index()
    {
        $series = Series::withCount('publishedPosts')
            ->has('publishedPosts')
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('frontend.series.index', compact('series'));
    }

    /*
    | Individual series page — /series/{slug}
    */
    public function show(string $slug)
    {
        $series = Series::where('slug', $slug)
            ->with(['user', 'publishedPosts.user', 'publishedPosts.category'])
            ->firstOrFail();

        /*
        | Only published posts shown to public.
        | Admin can see all — but we keep it simple here.
        */
        $posts = $series->publishedPosts;

        return view('frontend.series.show', compact('series', 'posts'));
    }
}
