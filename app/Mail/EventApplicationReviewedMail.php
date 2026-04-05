<?php

namespace App\Mail;

use App\Models\EventApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventApplicationReviewedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const OUTCOME_APPROVED = 'approved';

    public const OUTCOME_REJECTED = 'rejected';

    public function __construct(
        public EventApplication $application,
        public string $outcome
    ) {
        $this->application->loadMissing(['event.organization', 'user']);
    }

    public function envelope(): Envelope
    {
        $subject = $this->outcome === self::OUTCOME_APPROVED
            ? __('Mail subject event application approved', ['app' => config('app.name')])
            : __('Mail subject event application rejected', ['app' => config('app.name')]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-application-reviewed',
            text: 'emails.event-application-reviewed-text',
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
