<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    public function index(Request $request)
    {
        $query = Media::with('uploader')->latest();

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $media      = $query->paginate(48)->withQueryString();
        $totalSize  = Media::sum('size');
        $totalFiles = Media::count();

        return view('admin.media.index', compact('media', 'totalSize', 'totalFiles'));
    }

    /*
    | Upload via AJAX — returns JSON for the media library modal.
    */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file'   => ['required', 'file', 'image', 'max:4096'],
        ]);

        $media = $this->mediaService->store($request->file('file'));

        return response()->json([
            'success' => true,
            'media'   => [
                'id'             => $media->id,
                'url'            => $media->url,
                'filename'       => $media->filename,
                'original_name'  => $media->original_name,
                'formatted_size' => $media->formatted_size,
                'width'          => $media->width,
                'height'         => $media->height,
            ],
        ]);
    }

    /*
    | Single delete — called from grid action button.
    */
    public function destroy(Media $media): RedirectResponse
    {
        $this->mediaService->delete($media);

        return back()->with('success', 'Image deleted.');
    }

    /*
    | Bulk delete — called from the bulk action form.
    */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:media,id'],
        ]);

        $count = $this->mediaService->bulkDelete($request->ids);

        return back()->with('success', "{$count} " . Str::plural('image', $count) . ' deleted.');
    }

    /*
    | API endpoint for the TipTap media picker modal.
    | Returns paginated JSON — no blade rendering.
    */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Media::latest();

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $media = $query->paginate(20);

        return response()->json([
            'data' => $media->map(fn($m) => [
                'id'            => $m->id,
                'url'           => $m->url,
                'original_name' => $m->original_name,
                'formatted_size'=> $m->formatted_size,
                'width'         => $m->width,
                'height'        => $m->height,
            ]),
            'next_page_url' => $media->nextPageUrl(),
        ]);
    }
}
