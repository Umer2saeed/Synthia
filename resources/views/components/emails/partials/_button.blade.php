{{--
| Email Button Component
|
| Usage:
|   @include('emails.partials._button', [
|       'url'   => route('home'),
|       'label' => 'Read the Article',
|       'color' => '#4f46e5',     // optional, defaults to indigo
|   ])
|
| WHY inline styles on every element?
| Email clients strip external and embedded CSS.
| Every style must be inline to survive Gmail and Outlook.
|
| WHY a table for the button?
| Outlook does not support padding on <a> tags.
| The only way to make a clickable button with proper padding
| that works in Outlook is to use a table cell containing the link.
--}}

@php
    $buttonColor = $color ?? '#4f46e5'; // indigo-600 as default
    $textColor   = $textColor ?? '#ffffff';
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 24px auto;">
    <tr>
        <td style="border-radius: 10px; background-color: {{ $buttonColor }};">
            <a href="{{ $url }}"
               style="display: inline-block;
                      padding: 14px 32px;
                      font-size: 15px;
                      font-weight: 600;
                      color: {{ $textColor }};
                      text-decoration: none;
                      border-radius: 10px;
                      background-color: {{ $buttonColor }};
                      line-height: 1.5;
                      white-space: nowrap;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
