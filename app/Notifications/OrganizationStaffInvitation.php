<?php

namespace App\Notifications;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationStaffInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OrganizationInvitation $invitation,
        public string $acceptUrl,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $org = $this->invitation->organization;
        $orgName = $org !== null ? $org->name_en : config('app.name');

        return (new MailMessage)
            ->subject(__('Organization invitation email subject', ['org' => $orgName]))
            ->line(__('Organization invitation email body', [
                'org' => $orgName,
                'role' => __('invitation.role.'.$this->invitation->role),
            ]))
            ->action(__('Accept invitation'), $this->acceptUrl)
            ->line(__('Organization invitation expiry hint'));
    }
}
