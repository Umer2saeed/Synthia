<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $adminName,
        public readonly string $downloadUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' — Your Export Is Ready',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.export-ready');
    }
}
