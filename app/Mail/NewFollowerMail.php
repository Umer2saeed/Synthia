<?php

namespace App\Mail;

use App\Models\Follow;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewFollowerMail extends BaseMailable
{
    public function __construct(
        public Follow $follow
    ) {}

    protected function getSubject(): string
    {
        $followerName = $this->follow->follower->display_name ?? 'Someone';
        return "{$followerName} started following you on Synthia";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        // Total followers count for the author being followed
        $followersCount = $this->follow->following->followers()->count();

        return new Content(
            view: 'emails.new-follower',
            with: ['followersCount' => $followersCount],
        );
    }
}
