{{--
| Variables:
|   $user      (public) → the affected user
|   $newStatus (public) → 'active' or 'inactive'
--}}

<x-emails.layouts.master>

    {{-- Dynamic icon based on status --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px;
                       background-color: {{ $newStatus === 'active' ? '#f0fdf4' : '#fef2f2' }};
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                {{ $newStatus === 'active' ? '✅' : '⚠️' }}
            </td>
        </tr>
    </table>

    {{-- Heading --}}
    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        @if($newStatus === 'active')
            Your account has been activated
        @else
            Your account has been deactivated
        @endif
    </h1>

    {{-- Personalized message --}}
    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $user->name }}</strong>,
        @if($newStatus === 'active')
            your Synthia account has been activated. You now have full
            access to all features available to your account role.
        @else
            your Synthia account has been temporarily deactivated by
            an administrator. You will not be able to access the platform
            until your account is reactivated.
        @endif
    </p>

    {{-- Status panel --}}
    @include('components.emails.partials._panel', [
        'content' => 'Account status changed to: ' . strtoupper($newStatus),
        'color'   => $newStatus === 'active' ? '#f0fdf4' : '#fef2f2',
        'border'  => $newStatus === 'active' ? '#22c55e' : '#f87171',
    ])

    @if($newStatus === 'active')
        {{-- Activation CTA --}}
        @include('emails.partials._button', [
            'url'   => route('home'),
            'label' => 'Visit Synthia',
            'color' => '#16a34a',
        ])
    @else
        {{-- Deactivation: account details only --}}
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
               style="margin: 20px 0; background-color: #f8fafc;
                      border-radius: 8px; border: 1px solid #e2e8f0;">
            <tr>
                <td style="padding: 16px 20px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="font-size: 13px; color: #94a3b8; width: 80px; padding: 4px 0;">
                                Name
                            </td>
                            <td style="font-size: 13px; color: #1e293b; font-weight: 600; padding: 4px 0;">
                                {{ $user->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; color: #94a3b8; padding: 4px 0;">Email</td>
                            <td style="font-size: 13px; color: #1e293b; font-weight: 600; padding: 4px 0;">
                                {{ $user->email }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; color: #94a3b8; padding: 4px 0;">Status</td>
                            <td style="font-size: 13px; color: #dc2626; font-weight: 600; padding: 4px 0;">
                                Deactivated
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endif

    @include('components.emails.partials._divider')

    {{-- Contact prompt --}}
    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.7;">
        @if($newStatus === 'active')
            Welcome back! If you have any questions, reply to this email.
        @else
            If you believe this was done in error or have questions,
            please reply to this email to contact our team.
        @endif
    </p>

</x-emails.layouts.master>
