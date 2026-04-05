<?php

namespace App\Mail;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisputeOpenedStaffMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Dispute $dispute,
    ) {
        $this->dispute->loadMissing(['attendance.event', 'attendance.user', 'openedBy']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Mail subject dispute opened staff', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dispute-opened-staff',
            text: 'emails.dispute-opened-staff-text',
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
