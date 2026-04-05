<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public bool $approved,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->approved
            ? __('Mail subject organization approved', ['app' => config('app.name')])
            : __('Mail subject organization rejected', ['app' => config('app.name')]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-verification',
            text: 'emails.organization-verification-text',
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
