<?php

namespace App\Mail\Auth;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ResetPasswordMail extends BaseMailable
{
    /*
    | $user    → public so view can access $user->name directly
    | $resetUrl → the signed reset URL passed from the notification
    */
    public function __construct(
        public mixed   $user,
        public string $resetUrl
    ) {}

    protected function getSubject(): string
    {
        return 'Reset your Synthia password';
    }

    public function envelope(): Envelope
    {
        // Priority 1 = highest — password reset is urgent
        $this->priority = 1;

        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function content(): Content
    {
        // How long the reset link is valid — from config/auth.php
        $expiryMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $expiryText    = $expiryMinutes >= 60
            ? ($expiryMinutes / 60) . ' ' . \Str::plural('hour', $expiryMinutes / 60)
            : $expiryMinutes . ' minutes';

        return new Content(
            view: 'emails.auth.reset-password',
            with: ['expiryText' => $expiryText],
        );
    }
}
