<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /*
        | DejaVu Sans Mono is bundled inside DomPDF.
        | It is the ONLY monospace font DomPDF can render reliably.
        | Do NOT use 'monospace', 'Courier New', or 'Consolas' —
        | DomPDF will fail to resolve them and render bars instead of text.
        */
        @font-face {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
{{--            src: url('{{ base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSansMono.ttf') }}');--}}
            font-weight: normal;
            font-style: normal;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.7;
            padding: 40px;
        }

        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 28px;
        }

        .site-name {
            font-size: 10px;
            font-weight: 700;
            color: #6366f1;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .meta { font-size: 10px; color: #6b7280; }
        .meta span { margin-right: 16px; }

        .category-tag {
            display: inline-block;
            padding: 2px 8px;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .cover {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 6px;
            margin: 20px 0;
        }

        .content { font-size: 12px; line-height: 1.8; color: #374151; }
        .content h2 { font-size: 16px; font-weight: 700; margin: 20px 0 8px; color: #111827; }
        .content h3 { font-size: 14px; font-weight: 700; margin: 16px 0 6px; color: #111827; }
        .content p  { margin-bottom: 12px; }
        .content ul, .content ol { padding-left: 20px; margin-bottom: 12px; }
        .content li { margin-bottom: 4px; }

        .content blockquote {
            border-left: 3px solid #6366f1;
            padding: 8px 16px;
            margin: 16px 0;
            background: #f5f3ff;
            color: #4338ca;
            border-radius: 0 4px 4px 0;
        }

        /*
        | Inline code: use DejaVu Sans Mono explicitly.
        | DomPDF cannot use system fonts — only embedded TTF files.
        */
        .content code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10.5px;
            background: #f3f4f6;
            color: #1f2937;
            padding: 1px 5px;
            border-radius: 3px;
        }

        /*
        | Pre/code blocks: dark background with white text.
        | overflow: hidden prevents content spilling outside the box.
        | word-wrap: break-word wraps long lines so they don't overflow.
        */
        .content pre {
            background: #1f2937;
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 14px;
            overflow: hidden;
            word-wrap: break-word;
        }

        .content pre code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10px;
            color: #f9fafb;
            background: transparent;
            padding: 0;
            border-radius: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .content img { max-width: 100%; border-radius: 4px; }
        .content a   { color: #4f46e5; }

        .tags {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 10px;
            color: #6b7280;
            margin: 2px;
        }

        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <p class="site-name">{{ config('app.name') }}</p>
    <h1>{{ $post->title }}</h1>
    <div class="meta">
        <span>By <strong>{{ $post->user->name ?? '—' }}</strong></span>
        @if($post->category)
            <span><span class="category-tag">{{ $post->category->name }}</span></span>
        @endif
        <span>{{ $post->published_at?->format('d F Y') ?? $post->created_at->format('d F Y') }}</span>
        <span>{{ number_format($post->views) }} views</span>
    </div>
</div>

@if($post->cover_image)
    <img class="cover"
         src="{{ storage_path('app/public/' . $post->cover_image) }}"
         alt="{{ $post->title }}">
@endif

@if($post->ai_summary)
    <blockquote style="margin-bottom: 20px; font-style: italic;
                           border-left: 3px solid #6366f1; padding: 8px 16px;
                           background: #f5f3ff; color: #4338ca;">
        {{ $post->ai_summary }}
    </blockquote>
@endif

<div class="content">
    {!! $post->content !!}
</div>

@if($post->tags->isNotEmpty())
    <div class="tags">
        <strong style="font-size: 10px; color: #6b7280; text-transform: uppercase;
                           letter-spacing: 0.05em;">Tags</strong><br>
        <div style="margin-top: 6px;">
            @foreach($post->tags as $tag)
                <span class="tag">{{ $tag->name }}</span>
            @endforeach
        </div>
    </div>
@endif

<div class="footer">
    Exported from {{ config('app.name') }} on {{ now()->format('d M Y \a\t H:i') }}
    · {{ route('blog.post', $post->slug) }}
</div>

</body>
</html>
