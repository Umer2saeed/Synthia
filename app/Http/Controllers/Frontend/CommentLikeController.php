<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | toggle() — Like or unlike a comment
    |--------------------------------------------------------------------------
    |
    | FLOW:
    |   1. Find the comment (must be approved and belong to a published post)
    |   2. Block self-liking (cannot like your own comment)
    |   3. Check if like exists
    |      - EXISTS  → delete it (unlike)
    |      - MISSING → create it (like)
    |   4. Return updated like count and liked state
    |
    | WHY we return the count from DB and not increment/decrement in JS?
    | If two users like at the same moment, JS math would be wrong.
    | Fetching the real count from DB after the toggle ensures accuracy.
    */
    public function toggle(Request $request, Comment $comment): JsonResponse
    {
        /*
        | Only approved comments can be liked.
        | This prevents liking comments that are still in moderation.
        */
        if (!$comment->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot like this comment.',
            ], 403);
        }

        $userId = auth()->id();

        /*
        | Self-like prevention.
        | We check this server-side even though the button is hidden
        | in the UI — never trust the frontend alone for business rules.
        */
        if ($comment->user_id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot like your own comment.',
            ], 403);
        }

        /*
        | Check if this user already liked this comment.
        */
        $existingLike = CommentLike::where('user_id', $userId)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existingLike) {
            /*
            |------------------------------------------------------------------
            | Like exists → remove it (unlike)
            |------------------------------------------------------------------
            */
            $existingLike->delete();
            $liked = false;
        } else {
            /*
            |------------------------------------------------------------------
            | No like exists → create it (like)
            |------------------------------------------------------------------
            */
            CommentLike::create([
                'user_id'    => $userId,
                'comment_id' => $comment->id,
                'created_at' => now(),
            ]);
            $liked = true;
        }

        /*
        | Fetch the fresh count directly from the database.
        | This is always accurate regardless of race conditions.
        */
        $count = $comment->likes()->count();

        return response()->json([
            'success' => true,
            'liked'   => $liked,
            'count'   => $count,
        ]);
    }
}
