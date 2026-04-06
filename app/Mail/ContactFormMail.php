<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone?: string|null, subject: string, message: string, contact_type?: string, contact_type_label?: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function envelope(): Envelope
    {
        $typeBit = isset($this->payload['contact_type_label'])
            ? '['.$this->payload['contact_type_label'].'] '
            : '';

        return new Envelope(
            replyTo: [
                new Address($this->payload['email'], $this->payload['name']),
            ],
            subject: __('Contact form').': '.$typeBit.$this->payload['subject'].' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
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
