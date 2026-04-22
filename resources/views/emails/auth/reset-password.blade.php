<x-emails.layouts.master>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="width: 52px; height: 52px; background-color: #fef3c7;
                       border-radius: 14px; text-align: center;
                       vertical-align: middle; font-size: 26px; line-height: 52px;">
                🔑
            </td>
        </tr>
    </table>

    <h1 style="margin: 0 0 12px; font-size: 24px; font-weight: 800;
               color: #1e293b; line-height: 1.3;">
        Reset your password
    </h1>

    <p style="margin: 0 0 24px; font-size: 15px; color: #475569; line-height: 1.7;">
        Hi <strong style="color: #1e293b;">{{ $user->name }}</strong>,
        we received a request to reset your Synthia password.
        Click below to choose a new one.
    </p>

    @include('components.emails.partials._button', [
        'url'   => $resetUrl,
        'label' => 'Reset Password',
        'color' => '#dc2626',
    ])

    @include('components.emails.partials._panel', [
        'content' => "This link expires in {$expiryText}. After that you will need to request a new one.",
        'color'   => '#fffbeb',
        'border'  => '#f59e0b',
    ])

    @include('components.emails.partials._divider')

    <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
        If the button does not work, copy this link into your browser:
    </p>
    <p style="margin: 0 0 20px; font-size: 12px; word-break: break-all;">
        <a href="{{ $resetUrl }}" style="color: #6366f1;">{{ $resetUrl }}</a>
    </p>

    @include('components.emails.partials._divider')

    @include('components.emails.partials._panel', [
        'content' => 'If you did not request a password reset, no action is needed. Your password will not change.',
        'color'   => '#fef2f2',
        'border'  => '#fca5a5',
    ])

    <p style="margin: 16px 0 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
        This request was received from IP:
        <strong style="color: #64748b;">{{ request()->ip() }}</strong>
    </p>

</x-emails.layouts.master>
