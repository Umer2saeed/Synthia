<?php

namespace App\Console\Commands;

use App\Jobs\SendWeeklyDigestJob;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWeeklyDigest extends Command
{
    protected $signature   = 'digest:send-weekly';
    protected $description = 'Send the weekly digest email to all active verified users';

    public function handle(): int
    {
        $this->info('Building weekly digest...');

        /*
        |----------------------------------------------------------------------
        | Gather digest content — the same data goes to every user.
        | We build it once here and pass it to each job.
        | This avoids running the same expensive queries inside each job.
        |----------------------------------------------------------------------
        */

        /*
        | Top 5 most clapped posts from the past 7 days.
        | We join with the claps table and sum the count column.
        | withSum() adds claps_sum_count attribute to each post.
        */
        $topPosts = Post::with(['user', 'category'])
            ->published()
            ->where('published_at', '>=', now()->subDays(7))
            ->withSum('claps', 'count')
            ->orderByDesc('claps_sum_count')
            ->limit(5)
            ->get();

        /*
        | If no posts this week, skip sending the digest.
        | An empty digest email provides no value and annoys users.
        */
        if ($topPosts->isEmpty()) {
            $this->warn('No posts published this week. Skipping digest.');
            return Command::SUCCESS;
        }

        /*
        | New posts published this week (beyond the top 5)
        */
        $newPosts = Post::with(['user', 'category'])
            ->published()
            ->where('published_at', '>=', now()->subDays(7))
            ->latest('published_at')
            ->limit(10)
            ->get();

        /*
        | Weekly stats for the digest header
        */
        $stats = [
            'new_posts'    => Post::published()
                ->where('published_at', '>=', now()->subDays(7))
                ->count(),
            'new_users'    => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_posts'  => Post::published()->count(),
        ];

        /*
        | New authors who joined this week
        */
        $newAuthors = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('name', 'author'))
            ->where('created_at', '>=', now()->subDays(7))
            ->limit(3)
            ->get();

        /*
        |----------------------------------------------------------------------
        | Get all active verified users to send the digest to.
        | We chunk() processes them in batches of 100 to avoid loading
        | thousands of users into memory at once.
        |
        | chunk(100, function) fetches 100 users, processes them,
        | then fetches the next 100 — much more memory efficient
        | than User::all() for large user bases.
        |----------------------------------------------------------------------
        */
        $dispatchedCount = 0;

        User::where('status', 'active')
            ->whereNotNull('email_verified_at')
            ->chunk(100, function ($users) use (
                $topPosts, $newPosts, $stats, $newAuthors, &$dispatchedCount
            ) {
                foreach ($users as $user) {
                    /*
                    | Dispatch individual job for each user.
                    | Each job sends the digest email to one user.
                    | Jobs go to the 'low' queue — not urgent.
                    | The queue worker processes them gradually.
                    */
                    SendWeeklyDigestJob::dispatch(
                        $user,
                        $topPosts,
                        $newPosts,
                        $stats,
                        $newAuthors
                    );
                    $dispatchedCount++;
                }
            });

        $this->info("Digest jobs dispatched for {$dispatchedCount} users.");

        Log::info('SendWeeklyDigest: completed', [
            'users_queued' => $dispatchedCount,
            'top_posts'    => $topPosts->count(),
        ]);

        return Command::SUCCESS;
    }
}
