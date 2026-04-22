<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CommentApprovedMail extends BaseMailable
{
    public function __construct(
        public Comment $comment
    ) {}

    protected function getSubject(): string
    {
        $postTitle = \Str::limit($this->comment->post->title ?? 'a post', 40);
        return "Your comment on \"{$postTitle}\" has been approved";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        $postUrl        = route('blog.post', $this->comment->post->slug) . '#comments';
        $commentExcerpt = \Str::limit($this->comment->content, 200);

        return new Content(
            view: 'emails.comment-approved',
            with: [
                'postUrl'        => $postUrl,
                'commentExcerpt' => $commentExcerpt,
            ],
        );
    }
}
