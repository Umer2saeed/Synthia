<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestMail extends BaseMailable
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    | We inject a name to personalize the test email.
    | In real emails we inject User models, Post models, etc.
    */
    public function __construct(
        public string $name = 'Umer'
    ) {}

    /*
    |--------------------------------------------------------------------------
    | getSubject() — required by BaseMailable
    |--------------------------------------------------------------------------
    */
    protected function getSubject(): string
    {
        return 'Synthia Email Infrastructure Test';
    }

    /*
    |--------------------------------------------------------------------------
    | content() — defines what template renders this email
    |--------------------------------------------------------------------------
    | view: 'emails.test' → resources/views/emails/test.blade.php
    |
    | with: [] → data passed to the view
    | We use public properties ($this->name) which are automatically
    | available in the view without needing 'with'.
    */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
        );
    }
}
