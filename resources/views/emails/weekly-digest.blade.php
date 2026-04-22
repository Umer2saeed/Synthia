{{--
| Variables:
|   $user       (public) → the recipient
|   $topPosts   (public) → top 5 clapped posts this week
|   $newPosts   (public) → all new posts this week
|   $stats      (public) → array: new_posts, new_users, total_posts
|   $newAuthors (public) → new authors who joined this week
|   $weekOf              → week start date string
|   $weekEnd             → week end date string
--}}

<x-emails.layouts.master>

    {{-- Header --}}
    <p style="margin: 0 0 4px; font-size: 12px; font-weight: 600;
              color: #6366f1; text-transform: uppercase; letter-spacing: 0.08em;">
        Weekly Digest
    </p>

    <h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        Your Synthia weekly round-up
    </h1>

    <p style="margin: 0 0 24px; font-size: 14px; color: #94a3b8;">
        {{ $weekOf }} — {{ $weekEnd }}
    </p>

    <p style="margin: 0 0 24px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $user->name }}</strong>,
        here is what happened on Synthia this week.
    </p>

    {{-- ================================================
         STATS ROW
    ================================================ --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 28px; background-color: #f8fafc;
                  border-radius: 12px; border: 1px solid #e2e8f0;">
        <tr>
            {{-- New posts stat --}}
            <td style="padding: 20px; text-align: center;
                       border-right: 1px solid #e2e8f0; width: 33%;">
                <p style="margin: 0 0 4px; font-size: 28px; font-weight: 800; color: #6366f1;">
                    {{ $stats['new_posts'] }}
                </p>
                <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                    New {{ \Str::plural('Article', $stats['new_posts']) }}
                </p>
            </td>
            {{-- New users stat --}}
            <td style="padding: 20px; text-align: center;
                       border-right: 1px solid #e2e8f0; width: 33%;">
                <p style="margin: 0 0 4px; font-size: 28px; font-weight: 800; color: #6366f1;">
                    {{ $stats['new_users'] }}
                </p>
                <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                    New {{ \Str::plural('Member', $stats['new_users']) }}
                </p>
            </td>
            {{-- Total posts stat --}}
            <td style="padding: 20px; text-align: center; width: 33%;">
                <p style="margin: 0 0 4px; font-size: 28px; font-weight: 800; color: #6366f1;">
                    {{ $stats['total_posts'] }}
                </p>
                <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                    Total Articles
                </p>
            </td>
        </tr>
    </table>

    {{-- ================================================
         TOP POSTS THIS WEEK
    ================================================ --}}
    <p style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #1e293b;">
        🔥 Top reads this week
    </p>

    @foreach($topPosts as $index => $post)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
               style="margin: 0 0 12px; border: 1px solid #e2e8f0;
                      border-radius: 10px; overflow: hidden;">
            <tr>
                {{-- Rank number --}}
                <td style="width: 44px; background-color: {{ $index === 0 ? '#6366f1' : '#f8fafc' }};
                            text-align: center; vertical-align: middle;
                            font-size: 18px; font-weight: 800;
                            color: {{ $index === 0 ? '#ffffff' : '#94a3b8' }};
                            padding: 16px 12px;">
                    {{ $index + 1 }}
                </td>
                {{-- Post info --}}
                <td style="padding: 14px 16px; vertical-align: middle;">
                    <p style="margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #1e293b;">
                        <a href="{{ route('blog.post', $post->slug) }}"
                           style="color: #1e293b; text-decoration: none;">
                            {{ \Str::limit($post->title, 55) }}
                        </a>
                    </p>
                    <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                        by {{ $post->user->display_name ?? 'Unknown' }}
                        @if($post->category)
                            &middot; {{ $post->category->name }}
                        @endif
                        @if($post->claps_sum_count)
                            &middot; 👏 {{ number_format($post->claps_sum_count) }}
                        @endif
                    </p>
                </td>
            </tr>
        </table>
    @endforeach

    @include('components.emails.partials._button', [
        'url'   => route('blog'),
        'label' => 'Read All Articles',
    ])

    @include('components.emails.partials._divider')

    {{-- ================================================
         NEW AUTHORS THIS WEEK (only if any joined)
    ================================================ --}}
    @if($newAuthors->isNotEmpty())
        <p style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #1e293b;">
            ✍️ New authors this week
        </p>

        @foreach($newAuthors as $author)
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                   style="margin: 0 0 10px;">
                <tr>
                    <td style="padding: 12px 16px; background-color: #f8fafc;
                               border-radius: 8px; border: 1px solid #e2e8f0;">
                        <p style="margin: 0 0 2px; font-size: 14px;
                                   font-weight: 600; color: #1e293b;">
                            <a href="{{ route('author.profile', $author->username ?? $author->id) }}"
                               style="color: #6366f1; text-decoration: none;">
                                {{ $author->display_name }}
                            </a>
                        </p>
                        @if($author->bio)
                            <p style="margin: 0; font-size: 13px; color: #64748b;">
                                {{ \Str::limit($author->bio, 80) }}
                            </p>
                        @endif
                    </td>
                </tr>
            </table>
        @endforeach

        @include('components.emails.partials._divider')
    @endif

    {{-- ================================================
         UNSUBSCRIBE NOTICE
         IMPORTANT: Required by email regulations (CAN-SPAM, GDPR)
         Every bulk/marketing email must have an unsubscribe option.
    ================================================ --}}
    <p style="margin: 0; font-size: 12px; color: #94a3b8;
              text-align: center; line-height: 1.7;">
        You are receiving this weekly digest because you have an account on Synthia.<br>
        To stop receiving these emails, update your
        <a href="{{ route('frontend.profile.edit') }}"
           style="color: #6366f1; text-decoration: none;">
            notification preferences
        </a>
        in your profile settings.
    </p>

</x-emails.layouts.master>
