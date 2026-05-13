<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaService
{
    public function store(UploadedFile $file): Media
    {
        $original  = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;
        $path      = 'media/' . $filename;

        Storage::disk('public')->putFileAs('media', $file, $filename);

        [$width, $height] = $this->getDimensions(
            Storage::disk('public')->path($path),
            $file->getMimeType()
        );

        return Media::create([
            'uploaded_by'   => auth()->id(),
            'filename'      => $filename,
            'original_name' => $original,
            'disk'          => 'public',
            'path'          => $path,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $items = Media::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($items as $item) {
            $this->delete($item);
            $count++;
        }

        return $count;
    }

    private function getDimensions(string $fullPath, string $mimeType): array
    {
        if (!str_starts_with($mimeType, 'image/')) {
            return [null, null];
        }

        try {
            $manager = new ImageManager(new Driver());
            $image   = $manager->read($fullPath);
            return [$image->width(), $image->height()];
        } catch (\Exception) {
            return [null, null];
        }
    }
}
