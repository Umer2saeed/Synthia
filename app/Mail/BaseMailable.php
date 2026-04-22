<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

abstract class BaseMailable extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->getSubject(),
        );
    }

    abstract protected function getSubject(): string;

    protected int $priority = 3;
}
