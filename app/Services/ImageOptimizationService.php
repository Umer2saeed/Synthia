<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;

class ImageOptimizationService
{
    /*
    |--------------------------------------------------------------------------
    | Image Size Constants
    |--------------------------------------------------------------------------
    | POST_COVER: 1200×630 is the Open Graph standard ratio (1.91:1)
    | AVATAR:     400×400 square — displayed at max 96px, 400px gives retina
    | QUALITY:    80% — visually identical to 100% but ~40% smaller file size
    */
    const POST_COVER_WIDTH  = 1200;
    const POST_COVER_HEIGHT = 630;
    const AVATAR_SIZE       = 400;
    const QUALITY           = 80;

    /*
    |--------------------------------------------------------------------------
    | Constructor — inject ImageManager
    |--------------------------------------------------------------------------
    | ImageManager is registered as a singleton in AppServiceProvider.
    | Laravel automatically injects it here via the service container.
    | We do NOT instantiate it manually — the container handles it.
    */
    public function __construct(
        private ImageManager $imageManager
    ) {}

    /*
    |--------------------------------------------------------------------------
    | optimizePostCover()
    |--------------------------------------------------------------------------
    | Resizes and converts a post cover image to WebP format.
    |
    | @param string $storagePath  e.g. "posts/abc.jpg"
    | @return string              new path e.g. "posts/abc.webp"
    |                             returns original path if optimization fails
    */
    public function optimizePostCover(string $storagePath): string
    {
        try {
            /*
            | Build absolute filesystem paths.
            | Storage::disk('public')->path() converts storage-relative path
            | to the full path on disk that PHP can open directly.
            */
            $fullPath = Storage::disk('public')->path($storagePath);

            if (!Storage::disk('public')->exists($storagePath)) {
                Log::warning('ImageOptimizationService: file not found', [
                    'path' => $storagePath,
                ]);
                return $storagePath;
            }

            /*
            | Build the new WebP path by replacing the file extension.
            | "posts/abc123.jpg" → "posts/abc123.webp"
            | "posts/abc123.png" → "posts/abc123.webp"
            */
            $pathInfo    = pathinfo($storagePath);
            $newPath     = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $newFullPath = Storage::disk('public')->path($newPath);

            /*
            |------------------------------------------------------------------
            | Intervention Image v4 API:
            |------------------------------------------------------------------
            | $this->imageManager->read($path)
            |   → Opens the image file and returns an Image instance
            |
            | ->cover(width, height)
            |   → Resizes to fill exactly width×height
            |   → Crops any excess (like CSS background-size: cover)
            |   → Always produces exact dimensions regardless of source ratio
            |
            | ->toWebp(quality)
            |   → Converts to WebP format at given quality (0-100)
            |   → Returns an EncodedImage instance (not saved yet)
            |
            | ->save($path)
            |   → Writes the encoded image to the filesystem
            |------------------------------------------------------------------
            */
            $this->imageManager
                ->read($fullPath)
                ->cover(self::POST_COVER_WIDTH, self::POST_COVER_HEIGHT)
                ->toWebp(self::QUALITY)
                ->save($newFullPath);

            /*
            | Delete original only after successful save.
            | If ->save() threw an exception we would never reach this line
            | and the original file would be preserved safely.
            */
            if ($newPath !== $storagePath) {
                Storage::disk('public')->delete($storagePath);
            }

            Log::info('ImageOptimizationService: post cover optimized', [
                'original' => $storagePath,
                'new'      => $newPath,
                'old_size' => $this->humanFileSize($fullPath),
                'new_size' => $this->humanFileSize($newFullPath),
            ]);

            return $newPath;

        } catch (\Exception $e) {
            Log::error('ImageOptimizationService: post cover failed', [
                'path'  => $storagePath,
                'error' => $e->getMessage(),
            ]);

            /*
            | Return original path on failure — the post still has a cover,
            | just not optimized. Better than losing the image entirely.
            */
            return $storagePath;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | optimizeAvatar()
    |--------------------------------------------------------------------------
    | Resizes and converts an avatar image to WebP format.
    | Same logic as optimizePostCover() but for square avatars.
    |
    | @param string $storagePath  e.g. "avatars/abc.jpg"
    | @return string              new path e.g. "avatars/abc.webp"
    */
    public function optimizeAvatar(string $storagePath): string
    {
        try {
            $fullPath = Storage::disk('public')->path($storagePath);

            if (!Storage::disk('public')->exists($storagePath)) {
                Log::warning('ImageOptimizationService: avatar not found', [
                    'path' => $storagePath,
                ]);
                return $storagePath;
            }

            $pathInfo    = pathinfo($storagePath);
            $newPath     = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $newFullPath = Storage::disk('public')->path($newPath);

            /*
            | cover(400, 400) forces a perfect square crop.
            | Whatever the user uploads (portrait, landscape, square)
            | it becomes a 400×400 WebP — consistent avatar display.
            */
            $this->imageManager
                ->read($fullPath)
                ->cover(self::AVATAR_SIZE, self::AVATAR_SIZE)
                ->toWebp(self::QUALITY)
                ->save($newFullPath);

            if ($newPath !== $storagePath) {
                Storage::disk('public')->delete($storagePath);
            }

            Log::info('ImageOptimizationService: avatar optimized', [
                'original' => $storagePath,
                'new'      => $newPath,
                'old_size' => $this->humanFileSize($fullPath),
                'new_size' => $this->humanFileSize($newFullPath),
            ]);

            return $newPath;

        } catch (\Exception $e) {
            Log::error('ImageOptimizationService: avatar failed', [
                'path'  => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return $storagePath;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | humanFileSize() — convert bytes to readable string for logging
    |--------------------------------------------------------------------------
    */
    private function humanFileSize(string $fullPath): string
    {
        if (!file_exists($fullPath)) {
            return 'file not found';
        }

        $bytes = filesize($fullPath);

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
