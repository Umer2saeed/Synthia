{{--
| Variables:
|   $comment (public)  → has ->user (commenter) and ->post
|   $postUrl           → link to post + #comments anchor
|   $commentExcerpt    → truncated comment content
--}}

<x-emails.layouts.master>

    {{-- Icon --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #f0fdf4;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                ✅
            </td>
        </tr>
    </table>

    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        Your comment is now live!
    </h1>

    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $comment->user->name }}</strong>,
        your comment on
        <strong>"{{ $comment->post->title }}"</strong>
        has been reviewed and approved. It is now visible to all readers.
    </p>

    {{-- Comment preview --}}
    <p style="margin: 0 0 8px; font-size: 13px; font-weight: 600;
              color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
        Your comment:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #f8fafc; border-left: 3px solid #22c55e;
                       border-radius: 0 8px 8px 0; padding: 16px 20px;">
                <p style="margin: 0; font-size: 14px; color: #374151;
                           line-height: 1.7; font-style: italic;">
                    "{{ $commentExcerpt }}"
                </p>
                <p style="margin: 8px 0 0; font-size: 12px; color: #94a3b8;">
                    Posted {{ $comment->created_at->diffForHumans() }}
                </p>
            </td>
        </tr>
    </table>

    @include('components.emails.partials._button', [
        'url'   => $postUrl,
        'label' => 'View Your Comment',
        'color' => '#16a34a',
    ])

    @include('components.emails.partials._divider')

    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
        Thank you for contributing to the Synthia community.
        Your thoughts help make our content better.
    </p>

</x-emails.layouts.master>
