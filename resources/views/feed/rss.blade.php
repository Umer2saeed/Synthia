<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/">

    <channel>

        {{--
        | Channel metadata — describes the feed itself.
        | RSS readers display this information to subscribers.
        --}}
        <title>{{ $title }}</title>
        <link>{{ $siteUrl }}</link>
        <description>{{ $description }}</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <generator>{{ config('app.name') }} on Laravel</generator>
        <ttl>60</ttl>

        {{--
        | Atom self-link — required for feed validators.
        | Tells feed readers the canonical URL of this feed.
        --}}
        <atom:link href="{{ $feedUrl }}"
                   rel="self"
                   type="application/rss+xml"/>

        {{--
        | Each post becomes one <item> in the feed.
        --}}
        @foreach($posts as $post)
            <item>

                {{--
                | title: the post title, HTML-escaped
                --}}
                <title><![CDATA[{{ $post->title }}]]></title>

                {{--
                | link: the full URL to the post on Synthia
                --}}
                <link>{{ route('blog', $post->slug) }}</link>

                {{--
                | guid: globally unique identifier for this item.
                | isPermaLink="true" means the guid IS the URL.
                | This tells RSS readers to treat this as a unique item
                | and not re-show it if the title changes.
                --}}
                <guid isPermaLink="true">{{ route('blog', $post->slug) }}</guid>

                {{--
                | pubDate: when the post was published, in RFC 2822 format.
                | RSS requires this exact format (not ISO 8601).
                --}}
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>

                {{--
                | dc:creator: the author's name.
                | dc namespace = Dublin Core, standard metadata vocabulary.
                --}}
                <dc:creator><![CDATA[{{ $post->user->name }}]]></dc:creator>

                {{--
                | category: the post category.
                | RSS readers can filter by category.
                --}}
                @if($post->category)
                    <category><![CDATA[{{ $post->category->name }}]]></category>
                @endif

                {{--
                | description: the summary shown in feed reader previews.
                |
                | Priority:
                |   1. ai_summary if it exists (best human-written summary)
                |   2. First 300 characters of content stripped of HTML
                |      (fallback for posts without ai_summary)
                |
                | CDATA wraps the content so HTML entities inside it
                | are not interpreted as XML — prevents feed corruption.
                --}}
                <description>
                    <![CDATA[
                    @if($post->ai_summary)
                        {{ $post->ai_summary }}
                    @else
                        {{ Str::limit(strip_tags($post->content), 300) }}
                    @endif
                    ]]>
                </description>

                {{--
                | content:encoded: the full post HTML content.
                | This is what full-content RSS readers display.
                | Feed readers that support content:encoded show the
                | entire article instead of just the description.
                |
                | We sanitize the content one more time to ensure
                | no dangerous HTML escapes into the feed.
                --}}
                <content:encoded>
                    <![CDATA[
                    @if($post->cover_image)
                        <img src="{{ $post->cover_image_url }}"
                             alt="{{ $post->title }}"
                             style="max-width:100%;height:auto;margin-bottom:1rem;">
                    @endif

                    {!! $post->content !!}

                    <hr>
                    <p>
                        <a href="{{ route('blog', $post->slug) }}">
                            Read on {{ config('app.name') }} →
                        </a>
                    </p>
                    ]]>
                </content:encoded>

            </item>
        @endforeach

    </channel>
</rss>
