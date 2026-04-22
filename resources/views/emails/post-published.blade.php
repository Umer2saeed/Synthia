{{--
| Variables:
|   $post         (public) → post model with user, category loaded
|   $postUrl               → full public URL to the live post
|   $readTime              → estimated read time in minutes
|   $wasScheduled          → bool — true if this was a scheduled post
--}}

<x-emails.layouts.master>

    {{-- Icon --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #f0fdf4;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                🚀
            </td>
        </tr>
    </table>

    {{-- Heading --}}
    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        @if($wasScheduled)
            Your scheduled post just went live!
        @else
            Your post has been published!
        @endif
    </h1>

    {{-- Personalized intro --}}
    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $post->user->name }}</strong>,
        @if($wasScheduled)
            your scheduled post just went live automatically on Synthia.
            Readers can now discover and read it.
        @else
            great news! Your post has been published on Synthia and
            is now visible to all readers.
        @endif
    </p>

    {{-- Post details card --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 24px; background-color: #f8fafc;
                  border-radius: 12px; border: 1px solid #e2e8f0;">
        <tr>
            <td style="padding: 20px 24px;">

                {{-- Post title --}}
                <p style="margin: 0 0 12px; font-size: 18px; font-weight: 700;
                           color: #1e293b; line-height: 1.4;">
                    {{ $post->title }}
                </p>

                {{-- Meta row --}}
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        {{-- Category --}}
                        @if($post->category)
                            <td style="padding-right: 16px;">
                                <span style="display: inline-block; padding: 3px 10px;
                                             background-color: #ede9fe; color: #6d28d9;
                                             border-radius: 20px; font-size: 12px;
                                             font-weight: 600;">
                                    {{ $post->category->name }}
                                </span>
                            </td>
                        @endif
                        {{-- Read time --}}
                        <td>
                            <span style="font-size: 13px; color: #94a3b8;">
                                {{ $readTime }} min read
                            </span>
                        </td>
                        {{-- Published time --}}
                        <td style="padding-left: 16px;">
                            <span style="font-size: 13px; color: #94a3b8;">
                                {{ $post->published_at?->format('d M Y, H:i') }}
                            </span>
                        </td>
                    </tr>
                </table>

                {{-- AI Summary or excerpt --}}
                @php
                    $excerpt = $post->ai_summary
                        ?? \Str::limit(strip_tags($post->content), 150);
                @endphp
                <p style="margin: 12px 0 0; font-size: 14px; color: #64748b;
                           line-height: 1.6; border-top: 1px solid #e2e8f0;
                           padding-top: 12px;">
                    {{ $excerpt }}
                </p>

            </td>
        </tr>
    </table>

    {{-- Primary CTA --}}
    @include('components.emails.partials._button', [
        'url'   => $postUrl,
        'label' => 'View Your Live Post',
        'color' => '#16a34a',
    ])

    @include('components.emails.partials._divider')

    {{-- Share prompt --}}
    <p style="margin: 0 0 16px; font-size: 14px; font-weight: 700;
              color: #1e293b;">
        Share it with your audience:
    </p>

    {{-- Share buttons --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 24px;">
        <tr>
            {{-- Twitter/X share --}}
            <td style="padding-right: 8px;">
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($postUrl) }}&text={{ urlencode($post->title) }}"
                   style="display: inline-block; padding: 10px 20px;
                          background-color: #0f172a; color: #ffffff;
                          border-radius: 8px; font-size: 13px;
                          font-weight: 600; text-decoration: none;">
                    Share on X
                </a>
            </td>
            {{-- Copy link --}}
            <td>
                <a href="{{ $postUrl }}"
                   style="display: inline-block; padding: 10px 20px;
                          background-color: #f1f5f9; color: #334155;
                          border-radius: 8px; font-size: 13px;
                          font-weight: 600; text-decoration: none;
                          border: 1px solid #e2e8f0;">
                    Copy Link
                </a>
            </td>
        </tr>
    </table>

    @include('components.emails.partials._divider')

    {{-- Footer tip --}}
    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
        You can view all your published posts in your
        <a href="{{ route('admin.posts.index') }}"
           style="color: #6366f1; text-decoration: none;">
            admin dashboard
        </a>.
    </p>

</x-emails.layouts.master>
