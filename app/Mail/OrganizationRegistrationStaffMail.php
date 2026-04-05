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

class OrganizationRegistrationStaffMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public User $registeringUser,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Mail subject organization registration staff', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-registration-staff',
            text: 'emails.organization-registration-staff-text',
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
