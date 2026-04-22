<?php

namespace App\Mail\Auth;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyEmailMail extends BaseMailable
{
    /*
    |----------------------------------------------------------------------
    | We use mixed type instead of User because the notification system
    | passes a generic notifiable object, not strictly a User model.
    | Using mixed prevents type errors during notification processing.
    |----------------------------------------------------------------------
    */
    public function __construct(
        public mixed  $user,
        public string $verificationUrl
    ) {}

    protected function getSubject(): string
    {
        return 'Please verify your Synthia email address';
    }

    public function envelope(): Envelope
    {
        $this->priority = 1;
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        $expiryMinutes = \Config::get('auth.verification.expire', 60);
        $expiryText    = $expiryMinutes >= 60
            ? ($expiryMinutes / 60) . ' ' . \Str::plural('hour', $expiryMinutes / 60)
            : $expiryMinutes . ' minutes';

        return new Content(
            view: 'emails.auth.verify-email',
            with: ['expiryText' => $expiryText],
        );
    }
}
