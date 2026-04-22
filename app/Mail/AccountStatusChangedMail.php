<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountStatusChangedMail extends BaseMailable
{
    /*
    | $user      → public so view accesses $user->name directly
    | $newStatus → 'active' or 'inactive' — passed by the job
    */
    public function __construct(
        public User   $user,
        public string $newStatus
    ) {}

    protected function getSubject(): string
    {
        return $this->newStatus === 'active'
            ? 'Your Synthia account has been activated'
            : 'Your Synthia account has been deactivated';
    }

    public function envelope(): Envelope
    {
        /*
        | Priority 1 for account changes — users need to see this immediately.
        | An unexpected deactivation is a serious event for the user.
        */
        $this->priority = 1;
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-status-changed',
        );
    }
}
