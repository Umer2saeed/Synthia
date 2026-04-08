<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Clap;
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
        /*
        |----------------------------------------------------------------------
        | Step 1: Validate the post is published
        |----------------------------------------------------------------------
        | We only allow clapping on published posts.
        | If someone tries to clap on a draft post by guessing the URL,
        | we return a 404 error.
        |
        | The Post model binding automatically finds the post by ID.
        | We then check its status manually.
        */
        if ($post->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'You can only clap on published posts.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Step 2: Cannot clap on your own post
        |----------------------------------------------------------------------
        | Authors cannot clap on their own content.
        | This is a fairness rule — same as Medium's behavior.
        */
        if ($post->user_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot clap on your own post.',
            ], 403);
        }

        /*
        |----------------------------------------------------------------------
        | Step 3: Find or create the clap record
        |----------------------------------------------------------------------
        | firstOrCreate() does two things in one call:
        |
        | FIRST argument — the search conditions:
        |   Find a row where user_id = current user AND post_id = this post
        |
        | SECOND argument — default values if creating a new row:
        |   Set count = 0 when creating (we increment it next)
        |
        | DATABASE RESULT:
        |   First ever clap → INSERT INTO claps (user_id, post_id, count) VALUES (1, 5, 0)
        |   Subsequent clap → SELECT * FROM claps WHERE user_id=1 AND post_id=5
        */
        $clap = Clap::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ],
            [
                'count' => 0,
            ]
        );

        /*
        |----------------------------------------------------------------------
        | Step 4: Check if user has reached the maximum
        |----------------------------------------------------------------------
        | Clap::MAX_CLAPS_PER_USER = 50
        |
        | If the user has already clapped 50 times, we do not increment.
        | We still return a success response with maxed = true so
        | JavaScript can show a "max reached" state on the button.
        */
        if ($clap->count >= Clap::MAX_CLAPS_PER_USER) {
            return response()->json([
                'success'     => true,
                'maxed'       => true,
                'user_claps'  => $clap->count,
                'total_claps' => $post->totalClaps(),
                'message'     => 'You have reached the maximum claps for this post.',
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | Step 5: Increment the clap count
        |----------------------------------------------------------------------
        | increment('count') does this SQL in one query:
        |   UPDATE claps SET count = count + 1 WHERE id = {clap_id}
        |
        | This is safer than:
        |   $clap->count = $clap->count + 1;
        |   $clap->save();
        |
        | Because increment() is atomic — if two requests come in
        | simultaneously, they cannot overwrite each other.
        | The database handles the addition, not PHP.
        |
        | After increment() we refresh the model to get the updated count.
        */
        $clap->increment('count');
        $clap->refresh(); // reload the model to get the updated count value

        /*
        |----------------------------------------------------------------------
        | Step 6: Return JSON response
        |----------------------------------------------------------------------
        | JavaScript receives this and updates the UI:
        |   total_claps → the number shown publicly on the post
        |   user_claps  → how many times THIS user has clapped
        |   maxed       → whether this user has hit the 50 limit
        |   message     → optional feedback text
        */
        return response()->json([
            'success'     => true,
            'maxed'       => $clap->count >= Clap::MAX_CLAPS_PER_USER,
            'user_claps'  => $clap->count,
            'total_claps' => $post->totalClaps(),
            'message'     => 'Clapped!',
        ]);
    }
}
