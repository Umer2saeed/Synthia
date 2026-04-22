{{--
| Variables: $comment (public), $commentExcerpt (via with())
| $comment has: ->user (commenter), ->post, ->post->user (author), ->created_at
--}}

<x-emails.layouts.master>

    {{-- Icon --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #f0fdf4;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                💬
            </td>
        </tr>
    </table>

    {{-- Heading --}}
    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        New comment on your post
    </h1>

    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $comment->post->user->name }}</strong>,
        <strong>{{ $comment->user->display_name }}</strong>
        commented on your post.
    </p>

    {{-- Post title --}}
    @include('components.emails.partials._panel', [
        'content' => 'On: ' . $comment->post->title,
        'color'   => '#f8fafc',
        'border'  => '#6366f1',
    ])

    {{-- Comment preview --}}
    <p style="margin: 16px 0 8px; font-size: 13px; font-weight: 600;
              color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
        Their comment:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #f8fafc; border-left: 3px solid #e2e8f0;
                       border-radius: 0 8px 8px 0; padding: 16px 20px;">

                {{-- Commenter avatar + name --}}
                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                       style="margin-bottom: 10px;">
                    <tr>
                        <td style="font-size: 13px; font-weight: 600; color: #374151;">
                            {{ $comment->user->display_name }}
                        </td>
                        <td style="padding-left: 8px; font-size: 12px; color: #94a3b8;">
                            {{ $comment->created_at->diffForHumans() }}
                        </td>
                    </tr>
                </table>

                {{-- Comment text --}}
                <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.7;
                           font-style: italic;">
                    "{{ $commentExcerpt }}"
                </p>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    @include('components.emails.partials._button', [
        'url'   => route('blog.post', $comment->post->slug) . '#comments',
        'label' => 'View Comment',
    ])

    @include('components.emails.partials._divider')

    {{-- Commenter profile link --}}
    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
        View
        <a href="{{ route('author.profile', $comment->user->username ?? $comment->user->id) }}"
           style="color: #6366f1; text-decoration: none;">
            {{ $comment->user->display_name }}'s profile
        </a>
        to learn more about them.
    </p>

</x-emails.layouts.master>
