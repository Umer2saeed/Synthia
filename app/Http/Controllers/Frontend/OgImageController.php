<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\OgImageService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OgImageController extends Controller
{
    public function show(Post $post, OgImageService $ogImageService): Response
    {
        $path = 'og-images/' . $post->id . '.png';

        if (!Storage::disk('public')->exists($path)) {
            $ogImageService->generate($post);
        }

        $imageContent = Storage::disk('public')->get($path);

        return response($imageContent, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400'); // cache 24h in browser
    }
}
