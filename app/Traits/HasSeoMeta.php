<?php

namespace App\Traits;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

trait HasSeoMeta
{
    /*
    |--------------------------------------------------------------------------
    | buildSeo() — Build a consistent SEO data array
    |--------------------------------------------------------------------------
    | Called from every frontend controller method.
    | Returns an array that gets passed to the view as $seo.
    |
    | Parameters:
    |   $title       → page title (without app name — component adds it)
    |   $description → raw text — component strips HTML and limits to 160 chars
    |   $image       → absolute URL or storage path
    |   $type        → 'website' or 'article'
    |   $author      → author name string (for article type only)
    |   $publishedAt → Carbon instance or null (for article type only)
    */
    protected function buildSeo(
        string  $title,
        string  $description = '',
        string  $image       = '',
        string  $type        = 'website',
        ?string $author      = null,
        ?string $publishedAt = null,
    ): array {
        return [
            'title'       => $title,
            'description' => $description ?: config('app.name') . ' — ' . $title,
            'image'       => $image ?: asset('images/og-default.jpg'),
            'url'         => request()->url(),
            'type'        => $type,
            'author'      => $author,
            'publishedAt' => $publishedAt,
        ];
    }
}
