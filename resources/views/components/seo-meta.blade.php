{{--
|--------------------------------------------------------------------------
| SEO Meta Component
|--------------------------------------------------------------------------
| Usage in any layout:
|   <x-seo-meta
|       :title="$seo['title']"
|       :description="$seo['description']"
|       :image="$seo['image']"
|       :url="$seo['url']"
|       :type="$seo['type']"
|   />
|
| All props have sensible defaults so the site never has empty meta tags
| even if a controller forgets to pass SEO data.
--}}

@props([
    'title'       => config('app.name') . ' — A Modern Blog',
    'description' => 'Explore stories, insights, and tutorials on Synthia.',
    'image'       => asset('images/og-default.jpg'),
    'url'         => request()->url(),
    'type'        => 'website', // 'website' for pages, 'article' for posts
    'author'      => null,
    'publishedAt' => null,
])

@php
    /*
    |----------------------------------------------------------------------
    | Build the full title
    |----------------------------------------------------------------------
    | If the title already contains the app name, don't append it again.
    | Otherwise: "Post Title — Synthia"
    */
    $appName   = config('app.name', 'Synthia');
    $fullTitle  = str_contains($title, $appName)
                    ? $title
                    : $title . ' — ' . $appName;

    /*
    |----------------------------------------------------------------------
    | Sanitize description
    |----------------------------------------------------------------------
    | Strip HTML tags, collapse whitespace, limit to 160 characters.
    | Google truncates descriptions longer than 160 chars.
    */
    $cleanDescription = Str::limit(
        preg_replace('/\s+/', ' ', strip_tags($description)),
        160
    );

    /*
    |----------------------------------------------------------------------
    | Ensure image is an absolute URL
    |----------------------------------------------------------------------
    | og:image must be a full URL including https://, not a relative path.
    */
    $absoluteImage = str_starts_with($image, 'http')
                        ? $image
                        : asset($image);
@endphp

{{-- ===================== BASIC SEO ===================== --}}
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $cleanDescription }}">
<link rel="canonical" href="{{ $url }}">

{{-- ===================== OPEN GRAPH ===================== --}}
{{-- Used by Facebook, LinkedIn, WhatsApp, Telegram when sharing links --}}
<meta property="og:type"        content="{{ $type }}">
<meta property="og:url"         content="{{ $url }}">
<meta property="og:title"       content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $cleanDescription }}">
<meta property="og:image"       content="{{ $absoluteImage }}">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name"   content="{{ $appName }}">
<meta property="og:locale"      content="{{ str_replace('_', '-', app()->getLocale()) }}">

{{-- Article-specific Open Graph tags (only for single post pages) --}}
@if($type === 'article')
    @if($author)
        <meta property="article:author" content="{{ $author }}">
    @endif
    @if($publishedAt)
        <meta property="article:published_time" content="{{ $publishedAt }}">
    @endif
@endif

{{-- ===================== TWITTER CARDS ===================== --}}
{{-- Used by Twitter/X when someone shares a link --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $cleanDescription }}">
<meta name="twitter:image"       content="{{ $absoluteImage }}">

{{-- ===================== EXTRA ===================== --}}
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#6366f1">
