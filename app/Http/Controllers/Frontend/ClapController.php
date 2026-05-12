<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Clap;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClapController extends Controller
{
    public function clap(Request $request, Post $post): JsonResponse
    {
        if ($post->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'You can only clap on published posts.',
            ], 404);
        }


        if ($post->user_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot clap on your own post.',
            ], 403);
        }

        $clap = Clap::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ],
            [
                'count' => 0,
            ]
        );

        if ($clap->count >= Clap::MAX_CLAPS_PER_USER) {
            return response()->json([
                'success'     => true,
                'maxed'       => true,
                'user_claps'  => $clap->count,
                'total_claps' => $post->totalClaps(),
                'message'     => 'You have reached the maximum claps for this post.',
            ]);
        }

        $clap->increment('count');
        $clap->refresh(); // reload the model to get the updated count value

        app(BadgeService::class)->checkAndAward($post->user);

        Cache::forget('author.analytics.' . $post->user_id);


        return response()->json([
            'success'     => true,
            'maxed'       => $clap->count >= Clap::MAX_CLAPS_PER_USER,
            'user_claps'  => $clap->count,
            'total_claps' => $post->totalClaps(),
            'message'     => 'Clapped!',
        ]);
    }
}
