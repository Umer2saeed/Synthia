<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DiagnoseQueries extends Command
{
    protected $signature   = 'diagnose:queries';
    protected $description = 'Diagnose N+1 query problems across all pages';

    public function handle(): int
    {
        // Force prevention ON regardless of environment
        Model::preventLazyLoading(true);

        $this->info('=== N+1 QUERY DIAGNOSIS ===');
        $this->newLine();

        $this->testBlogListing();
        $this->testSinglePost();
        $this->testAdminPostsList();
        $this->testAdminCommentsList();
        $this->testAdminUsersList();
        $this->testDashboardRecent();

        return Command::SUCCESS;
    }

    private function testBlogListing(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $posts = Post::with(['user', 'category', 'tags'])
                ->published()
                ->paginate(12);

            foreach ($posts as $post) {
                $u = $post->user->name;
                $c = $post->category->name ?? 'none';
                $t = $post->tags->count();
            }

            $count   = count(DB::getQueryLog());
            $status  = $count <= 8 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Blog listing:      {$count} queries {$status}");

            if ($count > 8) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Blog listing: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function testSinglePost(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $post = Post::with(['user', 'category', 'tags'])
                ->published()
                ->first();

            if (!$post) {
                $this->warn('Single post: No published posts found to test');
                return;
            }

            $comments = $post->comments()
                ->approved()
                ->with(['user'])
                ->get();

            foreach ($comments as $comment) {
                $n = $comment->user->name;
            }

            $count  = count(DB::getQueryLog());
            $status = $count <= 8 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Single post:       {$count} queries {$status}");

            if ($count > 8) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Single post: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function testAdminPostsList(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $posts = Post::with(['user', 'category', 'tags'])
                ->withCount(['comments'])
                ->paginate(15);

            foreach ($posts as $post) {
                $u  = $post->user->name;
                $c  = $post->category->name ?? 'none';
                $t  = $post->tags->count();
                $cm = $post->comments_count;
            }

            $count  = count(DB::getQueryLog());
            $status = $count <= 8 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Admin posts list:  {$count} queries {$status}");

            if ($count > 8) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Admin posts: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function testAdminCommentsList(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $comments = Comment::with(['user', 'post'])
                ->paginate(20);

            foreach ($comments as $comment) {
                $u = $comment->user->name;
                $p = $comment->post->title ?? 'deleted';
            }

            $count  = count(DB::getQueryLog());
            $status = $count <= 6 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Admin comments:    {$count} queries {$status}");

            if ($count > 6) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Admin comments: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function testAdminUsersList(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $users = User::with(['roles'])
                ->withCount(['posts', 'comments'])
                ->paginate(20);

            foreach ($users as $user) {
                $r  = $user->roles->first()?->name ?? 'none';
                $pc = $user->posts_count;
                $cc = $user->comments_count;
            }

            $count  = count(DB::getQueryLog());
            $status = $count <= 6 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Admin users:       {$count} queries {$status}");

            if ($count > 6) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Admin users: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function testDashboardRecent(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $recentPosts = Post::with(['category'])
                ->latest()
                ->limit(6)
                ->get();

            $recentComments = Comment::with(['user', 'post'])
                ->whereHas('post')
                ->latest()
                ->limit(5)
                ->get();

            $recentUsers = User::with(['roles'])
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentPosts as $p) {
                $c = $p->category->name ?? 'none';
                $u = $p->user->name;
            }
            foreach ($recentComments as $c) {
                $u = $c->user->name;
                $p = $c->post->title ?? 'deleted';
            }
            foreach ($recentUsers as $u) {
                $r = $u->roles->first()?->name ?? 'none';
            }

            $count  = count(DB::getQueryLog());
            $status = $count <= 10 ? '✓ GOOD' : '✗ N+1 DETECTED';
            $this->line("Dashboard recent:  {$count} queries {$status}");

            if ($count > 10) {
                $this->showQueries();
            }
        } catch (\Illuminate\Database\LazyLoadingViolationException $e) {
            $this->error("Dashboard: LAZY LOAD VIOLATION — " . $e->getMessage());
        }
    }

    private function showQueries(): void
    {
        $this->newLine();
        $this->warn('  Queries fired:');
        foreach (DB::getQueryLog() as $i => $query) {
            $num = $i + 1;
            $sql = \Str::limit($query['query'], 120);
            $this->line("  [{$num}] {$sql}");
        }
        $this->newLine();
    }
}
