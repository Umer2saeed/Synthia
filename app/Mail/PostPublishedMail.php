<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PostPublishedMail extends BaseMailable
{
    /*
    | $post is public so the view can access:
    |   $post->title
    |   $post->user (author)
    |   $post->category
    |   $post->published_at
    |   $post->slug (for the live URL)
    */
    public function __construct(
        public Post $post
    ) {}

    protected function getSubject(): string
    {
        return "Your post \"{$this->post->title}\" is now live!";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->getSubject());
    }

    public function content(): Content
    {
        /*
        | Build the public URL to the live post.
        | This is what the author clicks to see their post live.
        */
        $postUrl = route('blog.post', $this->post->slug);

        /*
        | Calculate read time for display in the email.
        | Average reading speed: 200 words per minute.
        */
        $readTime = max(1, ceil(
            str_word_count(strip_tags($this->post->content)) / 200
        ));

        /*
        | Determine whether this was manually published or auto-scheduled.
        | We check if published_at is in the past by more than 2 minutes —
        | if the scheduled time was set in advance, it was a scheduled post.
        | This affects the email copy slightly.
        */
        $wasScheduled = $this->post->published_at
            && $this->post->published_at->lt(now()->subMinutes(2));

        return new Content(
            view: 'emails.post-published',
            with: [
                'postUrl'      => $postUrl,
                'readTime'     => $readTime,
                'wasScheduled' => $wasScheduled,
            ],
        );
    }
}
