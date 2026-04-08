<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    /*
    |--------------------------------------------------------------------------
    | $signature — the command name you type in terminal
    |--------------------------------------------------------------------------
    | This is what you type: php artisan posts:publish-scheduled
    | The namespace 'posts:' groups it with other post-related commands.
    */
    protected $signature = 'posts:publish-scheduled';

    /*
    |--------------------------------------------------------------------------
    | $description — shown in php artisan list
    |--------------------------------------------------------------------------
    | Describes what this command does when someone runs php artisan list
    | or php artisan help posts:publish-scheduled
    */
    protected $description = 'Publish all scheduled posts whose publish date has passed';

    /*
    |--------------------------------------------------------------------------
    | handle() — the main logic that runs when command is executed
    |--------------------------------------------------------------------------
    | This method runs every time the command is called — either manually
    | via terminal or automatically via the Laravel scheduler.
    |
    | Steps:
    |   1. Find all posts where status = 'scheduled' AND published_at <= now
    |   2. Update their status to 'published'
    |   3. Log what happened for debugging
    |   4. Output a summary to the terminal
    */
    public function handle(): int
    {
        $this->info('Checking for scheduled posts to publish...');

        /*
        |----------------------------------------------------------------------
        | Find posts ready to publish
        |----------------------------------------------------------------------
        | We need TWO conditions to be true:
        |   1. status must be 'scheduled' — not draft, not already published
        |   2. published_at must be in the past or exactly now
        |      (where() with '<=' means "less than or equal to now")
        |
        | We use now() which returns the current timestamp.
        | If published_at is null we skip it — a post with no publish date
        | should never be auto-published.
        */
        $posts = Post::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        /*
        |----------------------------------------------------------------------
        | Early exit if nothing to publish
        |----------------------------------------------------------------------
        */
        if ($posts->isEmpty()) {
            $this->info('No scheduled posts are ready to publish.');

            /*
            | Command::SUCCESS is an integer constant (0).
            | Returning 0 tells the system the command completed successfully.
            | Returning 1 (Command::FAILURE) signals an error.
            */
            return Command::SUCCESS;
        }

        /*
        |----------------------------------------------------------------------
        | Publish each post
        |----------------------------------------------------------------------
        | We loop instead of using a bulk update so we can:
        |   - Log each post individually
        |   - Fire model events (if you add them later e.g. notifications)
        |   - Handle errors per post without failing the whole batch
        */
        $publishedCount = 0;
        $failedCount    = 0;

        foreach ($posts as $post) {
            try {
                $post->update(['status' => 'published']);

                $publishedCount++;

                /*
                | $this->line() outputs a plain line to the terminal.
                | $this->info() outputs green text.
                | $this->error() outputs red text.
                | These only appear when you run the command manually.
                */
                $this->line("  ✓ Published: \"{$post->title}\" (ID: {$post->id})");

                /*
                |--------------------------------------------------------------
                | Log to Laravel's log file
                |--------------------------------------------------------------
                | Log::info() writes to storage/logs/laravel.log
                | This is crucial for scheduled tasks because they run in the
                | background with no terminal output — the log file is the
                | only record of what happened.
                */
                Log::info('Scheduled post published', [
                    'post_id'      => $post->id,
                    'title'        => $post->title,
                    'published_at' => $post->published_at,
                    'author_id'    => $post->user_id,
                ]);

            } catch (\Exception $e) {
                $failedCount++;

                $this->error("  ✗ Failed: \"{$post->title}\" (ID: {$post->id}) — {$e->getMessage()}");

                Log::error('Failed to publish scheduled post', [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        /*
        |----------------------------------------------------------------------
        | Summary output
        |----------------------------------------------------------------------
        */
        $this->newLine();
        $this->info("Done. Published: {$publishedCount}. Failed: {$failedCount}.");

        return Command::SUCCESS;
    }
}
