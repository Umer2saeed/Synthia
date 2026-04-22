<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewCommentMail extends BaseMailable
{
    /*
    | $comment is public — view accesses $comment->user, $comment->post etc.
    | We eager load relationships in the job before constructing this Mailable.
    */
    public function __construct(
        public Comment $comment
    ) {}

    protected function getSubject(): string
    {
        $commenterName = $this->comment->user->display_name ?? 'Someone';
        $postTitle     = \Str::limit($this->comment->post->title ?? 'your post', 40);

        return "{$commenterName} commented on \"{$postTitle}\"";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        // Excerpt of comment content — limit to avoid huge emails
        $commentExcerpt = \Str::limit($this->comment->content, 200);

        return new Content(
            view: 'emails.new-comment',
            with: ['commentExcerpt' => $commentExcerpt],
        );
    }
}
