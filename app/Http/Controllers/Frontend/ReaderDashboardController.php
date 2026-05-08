<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Clap;
use App\Models\Post;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReaderDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $dashboardData = $this->buildDashboardData($user);

        return view('frontend.reader-dashboard', array_merge(
            ['user' => $user],
            $dashboardData
        ));
    }

    private function buildDashboardData($user): array
    {
        /*
        | Cache only plain integer stats — never cache Eloquent collections.
        | Integers serialize and deserialize perfectly in file cache.
        | Eloquent models do not.
        */
        $stats = Cache::remember(
            'reader.dashboard.stats.' . $user->id,
            now()->addMinutes(10),
            function () use ($user) {
                return [
                    'posts_read'       => ReadingHistory::where('user_id', $user->id)->count(),
                    'authors_followed' => $user->following()->count(),
                    'comments_made'    => $user->comments()->count(),
                    'claps_given'      => Clap::where('user_id', $user->id)->sum('count'),
                ];
            }
        );

        /*
        | Collections are fetched fresh every request — no caching.
        | These queries are fast with proper indexes and small result sets.
        */
        $readingHistory = ReadingHistory::where('user_id', $user->id)
            ->where('read_at', '>=', now()->subDays(30))
            ->with([
                'post' => fn($q) => $q->withTrashed()->with(['user', 'category']),
            ])
            ->orderByDesc('read_at')
            ->limit(12)
            ->get()
            ->filter(fn($h) => $h->post !== null);

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with([
                'post' => fn($q) => $q->with(['user', 'category']),
            ])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->filter(fn($b) => $b->post !== null);

        $following = $user->following()
            ->with([
                'followedAuthor' => fn($q) => $q->withCount('posts'),
            ])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->pluck('followedAuthor')
            ->filter();

        return compact('stats', 'readingHistory', 'bookmarks', 'following');
    }
}
