<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewInOrganizationPortal(User $user, Event $event): bool
    {
        return $this->organizationMemberApproved($user)
            && (int) $event->organization_id === (int) $user->organization_id;
    }

    /**
     * Create/edit/delete event settings, checkpoint URL generation (owner + manager only; coordinators run roster ops).
     */
    public function configureInOrganizationPortal(User $user, Event $event): bool
    {
        return $user->hasAnyRole(['org-owner', 'org-manager'])
            && $this->organizationMemberApproved($user)
            && (int) $event->organization_id === (int) $user->organization_id;
    }

    public function deleteInOrganizationPortal(User $user, Event $event): bool|Response
    {
        if (! $user->hasAnyRole(['org-owner', 'org-manager'])) {
            return false;
        }

        if (! $this->organizationMemberApproved($user)) {
            return false;
        }

        if ((int) $event->organization_id !== (int) $user->organization_id) {
            return false;
        }

        if ($event->blocksOrganizationPortalDeletion()) {
            return Response::deny(__('Cannot delete event with volunteers or attendance records.'));
        }

        return true;
    }

    /**
     * Org staff may remove a volunteer from the roster when there is no attendance row yet (integrity).
     */
    public function removeVolunteerFromRosterInOrganizationPortal(User $user, Event $event, User $volunteer): bool|Response
    {
        if (! $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator'])) {
            return false;
        }

        if (! $this->organizationMemberApproved($user)) {
            return false;
        }

        if ((int) $event->organization_id !== (int) $user->organization_id) {
            return false;
        }

        if (! $event->userIsOnRoster($volunteer)) {
            return Response::deny(__('This volunteer is not on the roster for this event.'));
        }

        if ($event->attendances()->where('user_id', $volunteer->id)->exists()) {
            return Response::deny(__('Cannot remove a volunteer who already has attendance records for this event.'));
        }

        return true;
    }

    private function organizationMemberApproved(User $user): bool
    {
        return $user->organization_id !== null
            && $user->organization?->isApproved();
    }

    /**
     * Volunteers may add themselves to the roster while the event is still open (same window as public listing).
     */
    public function joinRoster(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        if ($event->event_ends_at < now()) {
            return false;
        }

        if ($event->userIsOnRoster($user)) {
            return true;
        }

        if (! $user->hasMinimumVolunteerProfileForCommitments()) {
            return false;
        }

        if (! $event->rosterAcceptsNewVolunteers()) {
            return false;
        }

        if ($event->application_required) {
            $application = $event->applicationForUser($user);

            if (! $application || ! $application->isApproved()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Submit or re-submit an application when the event requires organizer approval before joining the roster.
     */
    public function applyToEvent(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        if (! $user->hasMinimumVolunteerProfileForCommitments()) {
            return false;
        }

        if (! $event->application_required) {
            return false;
        }

        if ($event->event_ends_at < now()) {
            return false;
        }

        $existing = $event->applicationForUser($user);

        if ($existing === null) {
            return true;
        }

        return in_array($existing->status, [
            EventApplication::STATUS_REJECTED,
            EventApplication::STATUS_WITHDRAWN,
        ], true);
    }

    public function withdrawApplication(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        $application = $event->applicationForUser($user);

        return $application?->isPending() ?? false;
    }

    /**
     * Rostered volunteers may obtain a signed URL to open the mobile attendance checkpoint (same as QR flow).
     */
    public function accessAttendanceCheckpoint(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        return $event->userIsOnRoster($user);
    }

    /**
     * Volunteers may leave the roster until the event start time (organizer visibility).
     */
    public function leaveRoster(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        if (! $event->userIsOnRoster($user)) {
            return false;
        }

        return $event->event_starts_at->isFuture();
    }

    /**
     * Volunteers may bookmark upcoming opportunities from the public detail page.
     */
    public function saveOpportunity(User $user, Event $event): bool
    {
        if (! $user->hasRole('volunteer')) {
            return false;
        }

        return $event->event_ends_at >= now();
    }
}
