<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeMail extends BaseMailable
{
    public function __construct(
        public User $user
    ) {}

    protected function getSubject(): string
    {
        return "Welcome to Synthia, {$this->user->name}! 🎉";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function content(): Content
    {

        $roleName = $this->user->roles->first()?->name ?? 'reader';

        $roleDescription = match($roleName) {
            'admin'  => 'You have full administrative access to Synthia. You can manage posts, users, roles, and all platform settings.',
            'editor' => 'You have editor access. You can create, edit, and publish posts, manage categories, and moderate comments.',
            'author' => 'You have author access. You can write and submit posts for review, and engage with the community.',
            default  => 'You can read all published articles, leave comments, clap on posts, bookmark your favorites, and follow authors you love.',
        };

        $ctaLabel = match($roleName) {
            'admin', 'editor', 'author' => 'Go to Dashboard',
            default                      => 'Start Reading',
        };

        $ctaUrl = match($roleName) {
            'admin', 'editor', 'author' => route('admin.dashboard'),
            default                      => route('blog'),
        };

        return new Content(
            view: 'emails.welcome',
            with: [
                'roleName'        => $roleName,
                'roleDescription' => $roleDescription,
                'ctaLabel'        => $ctaLabel,
                'ctaUrl'          => $ctaUrl,
            ],
        );
    }
}
