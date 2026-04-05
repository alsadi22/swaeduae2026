<?php

namespace App\Policies;

use App\Models\EventApplication;
use App\Models\User;

class EventApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function review(User $user, EventApplication $eventApplication): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Approve or reject an application from the organization portal (event must belong to the user's org).
     */
    public function organizationPortalReview(User $user, EventApplication $eventApplication): bool
    {
        if (! $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator'])) {
            return false;
        }

        if ($user->organization_id === null || ! $user->organization?->isApproved()) {
            return false;
        }

        $eventApplication->loadMissing('event');

        return $eventApplication->event !== null
            && (int) $eventApplication->event->organization_id === (int) $user->organization_id;
    }
}
