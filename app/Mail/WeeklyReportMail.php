<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $stats
    ) {}

    public function envelope(): Envelope
    {
        $week = now()->subWeek()->format('d M') . ' – ' . now()->format('d M Y');
        return new Envelope(
            subject: config('app.name') . ' Weekly Report — ' . $week,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-report',
        );
    }
}
