<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Log;

class OptimizePostCoverJob extends BaseJob
{
    /*
    | We store both the Post model and the original path separately.
    | SerializesModels re-fetches Post fresh from DB when job runs.
    | $originalPath captures the exact path we uploaded at dispatch time —
    | if the editor changes the cover again before this job runs,
    | we detect the mismatch and skip to avoid processing the wrong file.
    */
    public function __construct(
        protected Post   $post,
        protected string $originalPath
    ) {
        $this->onQueue('low');
    }

    /*
    | handle() uses method injection — Laravel resolves ImageOptimizationService
    | from the service container automatically when calling this method.
    | The singleton registered in AppServiceProvider is injected here.
    */
    public function handle(ImageOptimizationService $optimizer): void
    {
        Log::info('OptimizePostCoverJob: starting', [
            'post_id'  => $this->post->id,
            'path'     => $this->originalPath,
        ]);

        // Re-fetch fresh model — SerializesModels already does this
        $post = $this->post->fresh();

        if (!$post) {
            Log::info('OptimizePostCoverJob: post deleted, skipping');
            return;
        }

        /*
        | If the cover_image on the post no longer matches what we were
        | given at dispatch time, the editor uploaded a new cover.
        | Skip this job — a newer OptimizePostCoverJob will handle the new image.
        */
        if ($post->cover_image !== $this->originalPath) {
            Log::info('OptimizePostCoverJob: cover changed, skipping', [
                'expected' => $this->originalPath,
                'current'  => $post->cover_image,
            ]);
            return;
        }

        // Run the optimization
        $newPath = $optimizer->optimizePostCover($this->originalPath);

        /*
        | Only update the database record if the path actually changed.
        | If optimizePostCover() returned the original path (failure),
        | no DB update needed.
        |
        | updateQuietly() updates without firing model events.
        | This prevents PostObserver from firing and clearing the cache
        | for what is essentially a background maintenance operation.
        */
        if ($newPath !== $this->originalPath) {
            $post->updateQuietly(['cover_image' => $newPath]);

            Log::info('OptimizePostCoverJob: completed', [
                'post_id'  => $post->id,
                'new_path' => $newPath,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('OptimizePostCoverJob: permanently failed', [
            'post_id' => $this->post->id,
            'path'    => $this->originalPath,
            'error'   => $exception->getMessage(),
        ]);
    }
}
