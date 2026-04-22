<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Clap;
use App\Models\Bookmark;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnosePageQueries extends Command
{
    protected $signature   = 'diagnose:page-queries';
    protected $description = 'Find all query sources on the blog listing page';

    public function handle(): int
    {
        $this->info('=== FINDING ALL QUERY SOURCES ===');
        $this->newLine();

        /*
        | We simulate every query that runs when the blog page loads.
        | Each section is isolated so we know exactly what fires what.
        */

        // Simulate a logged-in user
        $user = User::first();

        $this->section('1. Main posts query with eager loading', function () {
            Post::with(['user', 'category', 'tags'])
                ->published()
                ->paginate(12);
        });

        $this->section('2. Sidebar categories', function () {
            \App\Models\Category::withCount(['posts' => fn($q) => $q->published()])
                ->having('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->limit(10)
                ->get();
        });

        $this->section('3. Sidebar tags', function () {
            \App\Models\Tag::withCount(['posts' => fn($q) => $q->published()])
                ->having('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->limit(20)
                ->get();
        });

        $this->section('4. Clap counts per post (if called per post)', function () {
            $posts = Post::with(['user', 'category', 'tags'])
                ->published()
                ->paginate(12);

            // Simulate what totalClaps() does if called per post in blade
            foreach ($posts as $post) {
                Clap::where('post_id', $post->id)->sum('count');
            }
        });

        $this->section('5. Bookmark check per post (if called per post)', function () use ($user) {
            $posts = Post::with(['user', 'category', 'tags'])
                ->published()
                ->paginate(12);

            // Simulate what isBookmarkedBy() does if called per post in blade
            foreach ($posts as $post) {
                Bookmark::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->exists();
            }
        });

        $this->section('6. withCount approach (correct way)', function () use ($user) {
            // This is the correct way - all counts in one query
            Post::with(['user', 'category', 'tags'])
                ->withCount([
                    'claps',
                    'comments',
                    'bookmarks' => fn($q) => $q->where('user_id', $user->id ?? 0),
                ])
                ->published()
                ->paginate(12);
        });

        $this->newLine();
        $this->info('=== CONCLUSION ===');
        $this->line('If section 4 or 5 shows many queries, your blade post card');
        $this->line('is calling totalClaps() or isBookmarkedBy() per post.');
        $this->line('Fix: use withCount() in the controller instead.');

        return Command::SUCCESS;
    }

    private function section(string $name, callable $callback): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = DB::getQueryLog();
        $count   = count($queries);

        $this->line("  {$name}");
        $this->line("  → {$count} " . ($count === 1 ? 'query' : 'queries'));

        // Show each query so we know exactly what fired
        foreach ($queries as $i => $query) {
            $num = $i + 1;
            $sql = \Str::limit($query['query'], 100);
            $this->line("    [{$num}] {$sql}");
        }

        $this->newLine();
    }
}
