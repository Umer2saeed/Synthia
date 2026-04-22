<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Log;

class OptimizeAvatarJob extends BaseJob
{
    public function __construct(
        protected User   $user,
        protected string $originalPath
    ) {
        $this->onQueue('low');
    }

    public function handle(ImageOptimizationService $optimizer): void
    {
        Log::info('OptimizeAvatarJob: starting', [
            'user_id' => $this->user->id,
            'path'    => $this->originalPath,
        ]);

        $user = $this->user->fresh();

        if (!$user) {
            Log::info('OptimizeAvatarJob: user deleted, skipping');
            return;
        }

        // Skip if user changed their avatar since dispatch
        if ($user->avatar !== $this->originalPath) {
            Log::info('OptimizeAvatarJob: avatar changed, skipping', [
                'expected' => $this->originalPath,
                'current'  => $user->avatar,
            ]);
            return;
        }

        $newPath = $optimizer->optimizeAvatar($this->originalPath);

        if ($newPath !== $this->originalPath) {
            $user->updateQuietly(['avatar' => $newPath]);

            Log::info('OptimizeAvatarJob: completed', [
                'user_id'  => $user->id,
                'new_path' => $newPath,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('OptimizeAvatarJob: permanently failed', [
            'user_id' => $this->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
