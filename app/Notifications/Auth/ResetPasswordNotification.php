<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends ResetPassword
{
    /*
    |----------------------------------------------------------------------
    | NO ShouldQueue — sends synchronously like verification email.
    |
    | Password reset is time-sensitive. If the queue worker is not
    | running, the user never gets the reset link and thinks the
    | feature is broken. Synchronous sending guarantees delivery.
    |
    | The slight 200-400ms delay is completely acceptable for
    | a password reset flow where the user is already waiting.
    |----------------------------------------------------------------------
    */

    public function __construct(string $token)
    {
        parent::__construct($token);
        // Removed $this->onQueue('high') — no longer queued
    }

    public function toMail($notifiable)
    {
        $resetUrl   = $this->resetUrl($notifiable);
        $expiry     = Config::get(
            'auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire',
            60
        );
        $expiryText = $expiry >= 60
            ? ($expiry / 60) . ' ' . \Str::plural('hour', $expiry / 60)
            : $expiry . ' minutes';

        return (new MailMessage)
            ->subject('Reset your Synthia password')
            ->priority(1)
            ->view('emails.auth.reset-password', [
                'user'       => $notifiable,
                'resetUrl'   => $resetUrl,
                'expiryText' => $expiryText,
            ]);
    }
}
