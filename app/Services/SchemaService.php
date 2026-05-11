<?php

namespace App\Services;

use App\Models\Post;

class SchemaService
{
    /*
    | website() — WebSite schema for the homepage.
    | Enables Sitelinks Searchbox in Google results.
    */
    public function website(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => config('app.name'),
            'url'      => url('/'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => url('/blog') . '?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return $this->encode($schema);
    }

    /*
    | blogPosting() — Article + BlogPosting schema for a post page.
    | Enables rich results: author, date, image in Google search.
    */
    public function blogPosting(Post $post): string
    {
        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => $post->title,
            'description'      => $post->ai_summary
                ?? \Str::limit(strip_tags($post->content), 155),
            'image'            => [
                '@type' => 'ImageObject',
                'url'   => $post->cover_image
                    ? asset('storage/' . $post->cover_image)
                    : app(OgImageService::class)->getUrl($post),
            ],
            'author'           => [
                '@type' => 'Person',
                'name'  => $post->user->name ?? config('app.name'),
                'url'   => $post->user->username
                    ? route('author.profile', $post->user->username)
                    : url('/'),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => config('app.name'),
                'url'   => url('/'),
            ],
            'datePublished'    => $post->published_at?->toIso8601String()
                ?? $post->created_at->toIso8601String(),
            'dateModified'     => $post->updated_at->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => route('blog.post', $post->slug),
            ],
            'url'              => route('blog.post', $post->slug),
            'keywords'         => $post->tags->pluck('name')->implode(', '),
        ];

        if ($post->category) {
            $schema['articleSection'] = $post->category->name;
        }

        return $this->encode($schema);
    }

    /*
    | breadcrumbList() — BreadcrumbList schema for a post page.
    | Shows Home > Category > Post in Google search snippets.
    */
    public function breadcrumbList(Post $post): string
    {
        $items = [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Home',
                'item'     => url('/'),
            ],
            [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Blog',
                'item'     => route('blog'),
            ],
        ];

        $position = 3;

        if ($post->category) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $post->category->name,
                'item'     => route('blog.category', $post->category->slug),
            ];
        }

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $post->title,
            'item'     => route('blog.post', $post->slug),
        ];

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return $this->encode($schema);
    }

    private function encode(array $schema): string
    {
        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
