<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone?: string|null, topic: string, topic_label: string, subject: string, message: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->payload['email'], $this->payload['name']),
            ],
            subject: __('Support form').': '.$this->payload['subject'].' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
