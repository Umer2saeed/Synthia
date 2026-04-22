<?php

namespace App\Console\Commands;

use App\Jobs\OptimizeAvatarJob;
use App\Jobs\OptimizePostCoverJob;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class OptimizeExistingImages extends Command
{
    protected $signature   = 'images:optimize-existing';
    protected $description = 'Queue optimization jobs for all existing non-WebP images';

    public function handle(): int
    {
        // Queue post covers that are not already WebP
        $this->info('Queuing post cover jobs...');

        $posts = Post::whereNotNull('cover_image')
            ->where('cover_image', 'not like', '%.webp')
            ->get();

        foreach ($posts as $post) {
            OptimizePostCoverJob::dispatch($post, $post->cover_image);
            $this->line("  Queued post: {$post->title}");
        }

        $this->info("Post covers queued: {$posts->count()}");

        // Queue avatars that are not already WebP
        $this->newLine();
        $this->info('Queuing avatar jobs...');

        $users = User::whereNotNull('avatar')
            ->where('avatar', 'not like', '%.webp')
            ->get();

        foreach ($users as $user) {
            OptimizeAvatarJob::dispatch($user, $user->avatar);
            $this->line("  Queued user: {$user->name}");
        }

        $this->info("Avatars queued: {$users->count()}");
        $this->newLine();
        $this->info('Total: ' . ($posts->count() + $users->count()) . ' jobs dispatched.');
        $this->info('Now run: php artisan queue:work --queue=low');

        return Command::SUCCESS;
    }
}
