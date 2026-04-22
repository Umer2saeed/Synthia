{{--
| Variables:
|   $follow (public)      → has ->follower (who followed) and ->following (author)
|   $followersCount       → total followers the author now has
--}}

<x-emails.layouts.master>

    {{-- Icon --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #f5f3ff;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                🎉
            </td>
        </tr>
    </table>

    {{-- Heading --}}
    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        You have a new follower!
    </h1>

    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $follow->following->name }}</strong>,
        <strong>{{ $follow->follower->display_name }}</strong>
        is now following you on Synthia.
    </p>

    {{-- Follower card --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 24px; background-color: #f8fafc;
                  border-radius: 12px; border: 1px solid #e2e8f0;">
        <tr>
            <td style="padding: 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="vertical-align: middle;">

                            {{-- Follower info --}}
                            <p style="margin: 0 0 4px; font-size: 16px;
                                       font-weight: 700; color: #1e293b;">
                                {{ $follow->follower->display_name }}
                            </p>

                            @if($follow->follower->username)
                                <p style="margin: 0 0 6px; font-size: 13px;
                                           color: #94a3b8; font-family: monospace;">
                                    @ {{ $follow->follower->username }}
                                </p>
                            @endif

                            @if($follow->follower->bio)
                                <p style="margin: 0; font-size: 13px;
                                           color: #64748b; line-height: 1.5;">
                                    {{ \Str::limit($follow->follower->bio, 100) }}
                                </p>
                            @endif

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Followers count milestone --}}
    @include('components.emails.partials._panel', [
        'content' => "You now have {$followersCount} " . \Str::plural('follower', $followersCount) . " on Synthia. Keep publishing great content!",
        'color'   => '#f5f3ff',
        'border'  => '#8b5cf6',
    ])

    {{-- CTA --}}
    @include('components.emails.partials._button', [
        'url'   => route('author.profile', $follow->follower->username ?? $follow->follower->id),
        'label' => 'View Their Profile',
        'color' => '#7c3aed',
    ])

    @include('components.emails.partials._divider')

    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
        You are receiving this because someone followed you on Synthia.
    </p>

</x-emails.layouts.master>
