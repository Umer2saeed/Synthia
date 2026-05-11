<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewFollowerNotificationJob;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FollowController extends Controller
{

    public function toggle(Request $request, User $author): JsonResponse
    {
        /*
        |----------------------------------------------------------------------
        | Guard 1: Cannot follow yourself
        |----------------------------------------------------------------------
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
        */
        if ($currentUser->isFollowing($author)) {
            $currentUser->unfollow($author);
            $isNowFollowing = false;
            $message        = 'Unfollowed ' . $author->display_name;

        } else {
            $currentUser->follow($author);
            $isNowFollowing = true;
            $message        = 'Now following ' . $author->display_name;

            /*
            | Dispatch notification to the author being followed.
            | We find the Follow record just created so the job
            | has access to both follower and following relationships.
            */
            $followRecord = Follow::where('follower_id', $currentUser->id)
                ->where('following_id', $author->id)
                ->latest()
                ->first();

            if ($followRecord) {
                SendNewFollowerNotificationJob::dispatch($followRecord);
            }
        }

        /*
        |----------------------------------------------------------------------
        | Get fresh follower count
        |----------------------------------------------------------------------
        */
        $followersCount = $author->fresh()->followers()->count();
        /*
        | Clear the follower's activity feed cache after follow/unfollow.
        | Their feed changes immediately when they follow or unfollow someone.
        */
        for ($page = 1; $page <= 5; $page++) {
            Cache::forget('feed.user.' . auth()->id() . '.page.' . $page);
            // After follow/unfollow, clear dashboard cache alongside feed cache
            Cache::forget('reader.dashboard.stats.' . auth()->id());
        }

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
