{{--
| Test Email View
|
| This extends the master email layout using Blade's component slot system.
| <x-emails.layouts.master> wraps this content with the header and footer.
|
| Everything between the tags goes into {{ $slot }} in the layout.
--}}

<x-emails.layouts.master>

    {{-- Greeting --}}
    <p style="margin: 0 0 16px;
              font-size: 22px;
              font-weight: 700;
              color: #1e293b;
              line-height: 1.3;">
        Hello, {{ $name }}! 👋
    </p>

    {{-- Introduction --}}
    <p style="margin: 0 0 20px;
              font-size: 15px;
              color: #475569;
              line-height: 1.7;">
        This is a test email from Synthia confirming that your email
        infrastructure is working correctly. If you are reading this,
        everything is set up perfectly.
    </p>

    {{-- Info panel --}}
    @include('components.emails.partials._panel', [
        'content' => 'Email sent successfully from ' . config('app.url') . ' at ' . now()->format('d M Y H:i:s') . ' (' . config('app.timezone') . ')',
        'color'   => '#f0fdf4',
        'border'  => '#22c55e',
    ])

    {{-- Checklist --}}
    <p style="margin: 20px 0 12px;
              font-size: 14px;
              font-weight: 600;
              color: #374151;">
        Infrastructure verified:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin-bottom: 24px;">
        @foreach([
            'SMTP connection working',
            'Email layout rendering correctly',
            'Partial components loading',
            'Dynamic data passed to view',
            'Queue system ready for emails',
        ] as $item)
            <tr>
                <td style="padding: 6px 0; font-size: 14px; color: #475569;">
                    <span style="color: #22c55e; font-weight: 700; margin-right: 8px;">✓</span>
                    {{ $item }}
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Divider --}}
    @include('components.emails.partials._divider')

    {{-- Button --}}
    @include('components.emails.partials._button', [
        'url'   => config('app.url'),
        'label' => 'Visit Synthia',
    ])

    {{-- Footer note --}}
    <p style="margin: 20px 0 0;
              font-size: 13px;
              color: #94a3b8;
              text-align: center;
              line-height: 1.6;">
        This is an automated test email. No action is required.
    </p>

</x-emails.layouts.master>
