<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataErasureRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $optionalMessage,
    ) {}

    public function envelope(): Envelope
    {
        $reply = $this->user->email;

        return new Envelope(
            subject: __('Mail subject data erasure requested', ['app' => config('app.name')]),
            replyTo: [new Address($reply, (string) $this->user->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.data-erasure-requested',
            text: 'emails.data-erasure-requested-text',
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
