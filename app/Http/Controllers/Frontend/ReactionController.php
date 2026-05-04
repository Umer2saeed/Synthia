<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | toggle() — Add, switch, or remove a reaction
    |--------------------------------------------------------------------------
    |
    | FLOW:
    |   1. Validate the reaction type
    |   2. Find if user already has a reaction on this post
    |   3a. No reaction → create new one
    |   3b. Same type → delete it (toggle off)
    |   3c. Different type → update it (switch reaction)
    |   4. Return updated counts and current state
    |
    | WHY AJAX only?
    | Reactions should feel instant. A full page reload would break
    | the reading flow. AJAX lets us update just the reaction buttons
    | without affecting anything else on the page.
    */
    public function toggle(Request $request, Post $post): JsonResponse
    {
        /*
        | Validate the reaction type against our allowed list.
        | This prevents someone from sending type="hack" via API.
        */
        $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', Reaction::TYPES)],
        ]);

        $type   = $request->input('type');
        $userId = auth()->id();

        /*
        | Find existing reaction for this user on this post.
        | We use first() not firstOrFail() because no reaction is valid.
        */
        $existing = Reaction::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->first();

        if (!$existing) {
            /*
            |------------------------------------------------------------------
            | No existing reaction — create a new one
            |------------------------------------------------------------------
            */
            Reaction::create([
                'user_id'    => $userId,
                'post_id'    => $post->id,
                'type'       => $type,
                'created_at' => now(),
            ]);

            $activeType = $type;

        } elseif ($existing->type === $type) {
            /*
            |------------------------------------------------------------------
            | Same reaction clicked again — toggle it OFF (remove)
            |------------------------------------------------------------------
            | Reader already liked this post and clicks "like" again.
            | We remove the reaction entirely.
            */
            $existing->delete();
            $activeType = null;

        } else {
            /*
            |------------------------------------------------------------------
            | Different reaction — switch to the new type
            |------------------------------------------------------------------
            | Reader had "like" but now clicks "love".
            | We update the existing row instead of delete + insert
            | to preserve the created_at timestamp.
            */
            $existing->update(['type' => $type]);
            $activeType = $type;
        }

        /*
        | Return fresh counts after the change.
        | The frontend uses these to update all four button counts.
        */
        return response()->json([
            'success'     => true,
            'active_type' => $activeType,
            'counts'      => $post->getReactionCounts(),
            'total'       => $post->reactions()->count(),
        ]);
    }
}
