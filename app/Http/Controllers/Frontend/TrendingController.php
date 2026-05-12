<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\TrendingService;

class TrendingController extends Controller
{
    public function index(TrendingService $trendingService)
    {
        $posts = $trendingService->getTrending(20);

        return view('frontend.trending', compact('posts'));
    }
}
