<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Clap;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClapController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | clap() — Handle a clap request from the frontend
    |--------------------------------------------------------------------------
    |
    | REQUEST FLOW:
    |   1. User clicks the clap button on a post page
    |   2. JavaScript sends: POST /posts/{post}/clap
    |      Headers: X-CSRF-TOKEN, X-Requested-With: XMLHttpRequest
    |   3. Laravel routes this to ClapController@clap
    |   4. We validate the post exists and is published
    |   5. We find or create a clap record for this user+post
    |   6. We increment the count (up to MAX_CLAPS_PER_USER)
    |   7. We return JSON with updated counts
    |   8. JavaScript updates the UI instantly
    |
    | WHY firstOrCreate()?
    |   - If this user has NEVER clapped on this post:
    |     firstOrCreate() creates a new row with count=0
    |     then we increment to count=1
    |   - If this user HAS clapped before:
    |     firstOrCreate() returns the existing row
    |     then we increment their existing count
    |
    | This means there is always exactly ONE row per user+post combination.
    | The count column tracks how many times that user has clapped.
    */
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

        return response()->json([
            'success'     => true,
            'maxed'       => $clap->count >= Clap::MAX_CLAPS_PER_USER,
            'user_claps'  => $clap->count,
            'total_claps' => $post->totalClaps(),
            'message'     => 'Clapped!',
        ]);
    }
}
