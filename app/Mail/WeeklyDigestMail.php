<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WeeklyDigestMail extends BaseMailable
{
    public function __construct(
        public User       $user,
        public Collection $topPosts,
        public Collection $newPosts,
        public array      $stats,
        public Collection $newAuthors
    ) {}

    protected function getSubject(): string
    {
        $weekOf = now()->startOfWeek()->format('d M');
        return "Synthia Weekly — Top reads for the week of {$weekOf}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'weekOf'   => now()->startOfWeek()->format('d M Y'),
                'weekEnd'  => now()->endOfWeek()->format('d M Y'),
            ],
        );
    }
}
