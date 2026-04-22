<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->

    {{--
    |--------------------------------------------------------------------------
    | WHY INLINE STYLES?
    |--------------------------------------------------------------------------
    | Email clients strip <style> tags from <head>.
    | Gmail removes ALL <head> styles.
    | Outlook ignores many CSS properties entirely.
    | The ONLY reliable way to style emails is inline styles
    | on every single element.
    |
    | This is the fundamental difference between web and email HTML.
    --}}

    <style>
        /* Reset styles that some email clients add by default */
        * { box-sizing: border-box; }
        body, table, td, th, p, a, li { margin: 0; padding: 0; }
        body { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; }

        /* Dark mode support for email clients that support it */
        @media (prefers-color-scheme: dark) {
            .email-body { background-color: #1a1a2e !important; }
            .email-wrapper { background-color: #16213e !important; }
            .email-content { color: #e2e8f0 !important; }
        }

        /* Responsive for mobile */
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .email-padding { padding: 24px 16px !important; }
            .btn { display: block !important; text-align: center !important; }
        }
    </style>
</head>

{{--
| The body uses a table-based layout.
| WHY TABLES? Because Outlook (which has 400 million users) uses
| Microsoft Word's rendering engine, which supports tables but
| not flexbox, grid, or many modern CSS properties.
| Tables are the universal layout system for email.
--}}
<body style="margin:0; padding:0; background-color:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" class="email-body">

{{-- Outer wrapper table — centers the email --}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;" class="email-body">
    <tr>
        <td align="center" style="padding: 40px 20px;" class="email-padding">

            {{-- Inner container — max width 600px (standard email width) --}}
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;" class="email-container">

                {{-- ==========================================
                     HEADER — Logo and navigation
                     ========================================== --}}
                <tr>
                    <td style="padding-bottom: 24px; text-align: center;">
                        <a href="{{ config('app.url') }}"
                           style="text-decoration:none;">
                                <span style="font-size: 28px; font-weight: 800; color: #4f46e5; letter-spacing: -0.5px;">
                                    Synthia
                                </span>
                        </a>
                    </td>
                </tr>

                {{-- ==========================================
                     MAIN CONTENT CARD
                     ========================================== --}}
                <tr>
                    <td style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);" class="email-wrapper">

                        {{-- Content padding --}}
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding: 40px 40px 32px;" class="email-padding email-content">

                                    {{--
                                    | $slot is the Blade component slot.
                                    | Each individual email fills this area
                                    | with its specific content.
                                    | Think of this as {{ $slot }} in components.
                                    --}}
                                    {{ $slot }}

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- ==========================================
                     FOOTER — Legal, unsubscribe, social
                     ========================================== --}}
                <tr>
                    <td style="padding-top: 32px; text-align: center;">

                        {{-- Divider --}}
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="border-top: 1px solid #e2e8f0; padding-top: 24px;">

                                    {{-- Footer links --}}
                                    <p style="margin:0 0 8px; font-size:13px; color:#94a3b8;">
                                        <a href="{{ config('app.url') }}"
                                           style="color:#6366f1; text-decoration:none;">
                                            Visit Synthia
                                        </a>
                                        &nbsp;&middot;&nbsp;
                                        <a href="{{ config('app.url') }}/blog"
                                           style="color:#6366f1; text-decoration:none;">
                                            Read the Blog
                                        </a>
                                    </p>

                                    {{-- Legal text --}}
                                    <p style="margin:0 0 8px; font-size:12px; color:#94a3b8; line-height:1.6;">
                                        You are receiving this email because you have an account on
                                        <a href="{{ config('app.url') }}" style="color:#6366f1; text-decoration:none;">Synthia</a>.
                                    </p>

                                    {{-- Address --}}
                                    <p style="margin:0; font-size:12px; color:#cbd5e1;">
                                        &copy; {{ date('Y') }} Synthia. All rights reserved.
                                    </p>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

            </table>
            {{-- End inner container --}}

        </td>
    </tr>
</table>
{{-- End outer wrapper --}}

</body>
</html>
