<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | toggle() — Follow or unfollow an author (AJAX)
    |--------------------------------------------------------------------------
    |
    | COMPLETE REQUEST FLOW:
    |
    | 1. User clicks Follow/Following button on an author profile page
    | 2. JavaScript sends: POST /authors/{author}/follow
    |    Headers: X-CSRF-TOKEN, X-Requested-With: XMLHttpRequest
    | 3. Laravel routes to FollowController@toggle
    |    Route model binding automatically finds the User by ID
    | 4. We run multiple validation checks (guards)
    | 5. We check if currently following
    |    YES → unfollow → return { following: false, count: X }
    |    NO  → follow   → return { following: true,  count: X }
    | 6. JavaScript updates the button and follower count
    |
    | WHY {author} in the URL instead of /follows?
    | Because we are acting ON a specific author — the author is the
    | resource. /authors/{author}/follow reads naturally:
    | "perform a follow action on this author"
    | This follows RESTful URL design principles.
    */
    public function toggle(Request $request, User $author): JsonResponse
    {
        /*
        |----------------------------------------------------------------------
        | Guard 1: Cannot follow yourself
        |----------------------------------------------------------------------
        | If a user tries to follow their own profile, we block it.
        | The UI already hides the button for own profile, but we
        | always validate on the server — never trust the client alone.
        */
        if (auth()->id() === $author->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself.',
            ], 403);
        }

        /*
        |----------------------------------------------------------------------
        | Guard 2: Author must be an active user
        |----------------------------------------------------------------------
        | Do not allow following inactive/banned users.
        */
        if ($author->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This user account is not active.',
            ], 403);
        }

        $currentUser = auth()->user();

        /*
        |----------------------------------------------------------------------
        | Check current follow state and toggle
        |----------------------------------------------------------------------
        | isFollowing() queries:
        |   SELECT EXISTS(
        |     SELECT 1 FROM follows
        |     WHERE follower_id = {me} AND following_id = {author}
        |   )
        |
        | Based on the result we call follow() or unfollow()
        | which are the helper methods we added to the User model.
        */
        if ($currentUser->isFollowing($author)) {
            /*
            |------------------------------------------------------------------
            | Currently following → UNFOLLOW
            |------------------------------------------------------------------
            | unfollow() runs:
            |   DELETE FROM follows
            |   WHERE follower_id = {me} AND following_id = {author}
            */
            $currentUser->unfollow($author);
            $isNowFollowing = false;
            $message        = 'Unfollowed ' . $author->display_name;

        } else {
            /*
            |------------------------------------------------------------------
            | Not following → FOLLOW
            |------------------------------------------------------------------
            | follow() runs:
            |   INSERT INTO follows (follower_id, following_id, created_at)
            |   VALUES ({me}, {author}, NOW())
            */
            $currentUser->follow($author);
            $isNowFollowing = true;
            $message        = 'Now following ' . $author->display_name;
        }

        /*
        |----------------------------------------------------------------------
        | Get fresh follower count
        |----------------------------------------------------------------------
        | After the follow/unfollow we count fresh from the database.
        | We do NOT use $author->followers_count because that was
        | loaded at the start of the request and is now stale.
        | fresh()->followers()->count() re-queries the DB for accuracy.
        */
        $followersCount = $author->fresh()->followers()->count();

        return response()->json([
            'success'         => true,
            'following'       => $isNowFollowing,
            'followers_count' => $followersCount,
            'message'         => $message,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | following() — Show who the current user is following
    |--------------------------------------------------------------------------
    |
    | This is a regular page load — not AJAX.
    | Shows a list of authors the current user follows.
    |
    | QUERY FLOW:
    | 1. Get all Follow records where follower_id = current user
    | 2. Eager load the 'following' relationship (the author User model)
    | 3. For each followed author, load their published post count
    | 4. Order by most recently followed first
    | 5. Paginate the results
    */
    public function following()
    {
        $follows = auth()->user()
            ->following()
            ->with([
                /*
                | Load the User model of the person being followed.
                | 'following' here refers to the Follow model's
                | following() relationship — the author User model.
                */
                'following',
                'following.roles',
            ])
            ->withCount([
                /*
                | For each followed author, count their published posts.
                | This gives us a number to show on their card.
                */
                'following as following_posts_count' => function ($q) {
                    $q->whereHas('posts', fn($p) => $p->published());
                },
            ])
            ->latest()
            ->paginate(16);

        return view('frontend.following', compact('follows'));
    }

    /*
    |--------------------------------------------------------------------------
    | followers() — Show who follows the current user
    |--------------------------------------------------------------------------
    |
    | Mirror of following() but from the opposite direction.
    | Shows a list of users who follow the current user.
    */
    public function followers()
    {
        $follows = auth()->user()
            ->followers()
            ->with([
                /*
                | Load the User model of the person who is following.
                | 'follower' refers to the Follow model's
                | follower() relationship — the follower User model.
                */
                'follower',
                'follower.roles',
            ])
            ->latest()
            ->paginate(16);

        return view('frontend.followers', compact('follows'));
    }
}
