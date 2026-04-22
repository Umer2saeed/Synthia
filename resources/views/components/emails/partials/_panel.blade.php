{{--
| Email Info Panel Component
| A highlighted box for important information
|
| Usage:
|   @include('emails.partials._panel', [
|       'content' => 'Your post "Laravel Tips" has been published.',
|       'color'   => '#f0f9ff',  // optional background color
|       'border'  => '#0ea5e9',  // optional border color
|   ])
--}}

@php
    $bgColor     = $color  ?? '#f8fafc';
    $borderColor = $border ?? '#e2e8f0';
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
       style="margin: 20px 0;">
    <tr>
        <td style="background-color: {{ $bgColor }};
                   border-left: 4px solid {{ $borderColor }};
                   border-radius: 0 8px 8px 0;
                   padding: 16px 20px;">
            <p style="margin: 0;
                      font-size: 14px;
                      color: #374151;
                      line-height: 1.6;">
                {{ $content }}
            </p>
        </td>
    </tr>
</table>
