<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\CacheService;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class ActivityFeedController extends Controller
{
    /*
    | Activity feed is only for authenticated users.
    | The route already enforces auth middleware.
    */
    public function index(Request $request)
    {
        $user    = auth()->user();
        $page    = (int) $request->input('page', 1);
        $perPage = 12;

        /*
        | Get IDs of authors this user follows.
        | The follows table has follower_id and following_id columns.
        | We want posts from the people this user is FOLLOWING.
        */
        $followingIds = $user->following()->pluck('following_id')->toArray();

        /*
        | If user follows nobody, skip all queries and show empty state.
        */
        if (empty($followingIds)) {
            return view('frontend.activity-feed', [
                'posts'        => new LengthAwarePaginator([], 0, $perPage, $page),
                'followingIds' => [],
                'isEmpty'      => true,
            ]);
        }

        /*
        | Cache the post IDs for this user's feed page.
        | We cache IDs only — not full Eloquent models.
        | Reason: models with relationships cause deserialization issues in file cache.
        */
        $cacheKey = 'feed.user.' . $user->id . '.page.' . $page;

        $cached = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($followingIds, $page, $perPage) {
            $paginator = Post::published()
                ->whereIn('user_id', $followingIds)
                ->latest('published_at')
                ->paginate($perPage, ['id'], 'page', $page);

            return [
                'ids'   => $paginator->pluck('id')->toArray(),
                'total' => $paginator->total(),
            ];
        });

        /*
        | Re-fetch fresh models using cached IDs.
        | Same pattern used in BlogController for consistency.
        */
        $items = collect();

        if (!empty($cached['ids'])) {
            $items = Post::with(['user', 'category', 'tags'])
                ->withCount(['claps', 'comments'])
                ->whereIn('id', $cached['ids'])
                ->get()
                ->sortBy(fn($post) => array_search($post->id, $cached['ids']))
                ->values();
        }

        $posts = new LengthAwarePaginator(
            $items,
            $cached['total'],
            $perPage,
            $page,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('frontend.activity-feed', [
            'posts'        => $posts,
            'followingIds' => $followingIds,
            'isEmpty'      => false,
        ]);
    }
}
