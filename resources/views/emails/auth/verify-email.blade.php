{{--
| This view is rendered by MailMessage::view()
| Variables: $user, $verificationUrl, $expiryText
| We use our master layout via the component system
--}}

<x-emails.layouts.master>

    {{-- Icon --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #eff6ff;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                ✉️
            </td>
        </tr>
    </table>

    {{-- Heading --}}
    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        Verify your email address
    </h1>

    <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $user->name }}</strong>,
        thanks for registering on Synthia. Please verify your email
        address by clicking the button below.
    </p>

    {{-- Verify button --}}
    @include('components.emails.partials._button', [
        'url'   => $verificationUrl,
        'label' => 'Verify Email Address',
    ])

    {{-- Expiry warning --}}
    @include('components.emails.partials._panel', [
        'content' => "This link expires in {$expiryText}. You can request a new one from the login page.",
        'color'   => '#fffbeb',
        'border'  => '#f59e0b',
    ])

    @include('components.emails.partials._divider')

    {{-- Raw URL fallback --}}
    <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
        If the button does not work, copy this link into your browser:
    </p>
    <p style="margin: 0 0 20px; font-size: 12px; word-break: break-all;">
        <a href="{{ $verificationUrl }}" style="color: #6366f1;">
            {{ $verificationUrl }}
        </a>
    </p>

    @include('components.emails.partials._divider')

    <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.7;">
        If you did not create a Synthia account, please ignore this email.
    </p>

</x-emails.layouts.master>
