<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /*
    |----------------------------------------------------------------------
    | IMPORTANT DECISIONS:
    |----------------------------------------------------------------------
    | 1. NO ShouldQueue — sends synchronously, guaranteed delivery
    | 2. NO custom Mailable — we use MailMessage directly
    |    This avoids ALL the type compatibility issues we have been hitting
    | 3. We build the email content directly in toMail()
    |    Simpler, more reliable, easier to debug
    |
    | WHY abandon the custom Mailable approach?
    | The VerifyEmail parent class is tightly coupled to MailMessage.
    | Trying to return a different type causes PHP signature errors.
    | Using MailMessage directly is the correct approach for notifications.
    | We still get full control over the email content and styling.
    |----------------------------------------------------------------------
    */

    public function toMail($notifiable)
    {
        /*
        | Generate the signed verification URL.
        | This is inherited from Laravel's VerifyEmail class.
        | It creates a URL with a cryptographic signature using APP_KEY.
        */
        $verificationUrl = $this->verificationUrl($notifiable);
        /*
        | Build the email using Laravel's MailMessage fluent interface.
        | view() renders a Blade template — we pass the master layout approach.
        | This is the CORRECT way to style notification emails.
        */
        return (new MailMessage)
            ->subject('Please verify your Synthia email address')
            ->view(
                'emails.auth.verify-email',
                [
                    'user'            => $notifiable,
                    'verificationUrl' => $verificationUrl,
                    'expiryText'      => $this->getExpiryText(),
                ]
            );
    }

    /*
    | Calculate human-readable expiry time from config
    */
    private function getExpiryText(): string
    {
        $minutes = Config::get('auth.verification.expire', 60);

        return $minutes >= 60
            ? ($minutes / 60) . ' ' . \Str::plural('hour', $minutes / 60)
            : $minutes . ' minutes';
    }
}
