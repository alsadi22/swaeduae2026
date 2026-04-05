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

class DisputeResolvedVolunteerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Dispute $dispute,
    ) {
        $this->dispute->loadMissing(['attendance.event', 'attendance.user']);
    }

    public function envelope(): Envelope
    {
        $subject = $this->dispute->status === Dispute::STATUS_RESOLVED
            ? __('Mail subject dispute resolved', ['app' => config('app.name')])
            : __('Mail subject dispute dismissed', ['app' => config('app.name')]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dispute-resolved-volunteer',
            text: 'emails.dispute-resolved-volunteer-text',
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
