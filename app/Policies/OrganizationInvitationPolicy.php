<?php

namespace App\Policies;

use App\Models\OrganizationInvitation;
use App\Models\User;

class OrganizationInvitationPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('org-owner')
            && $user->organization !== null
            && $user->organization->isApproved();
    }

    public function delete(User $user, OrganizationInvitation $invitation): bool
    {
        if ($invitation->isAccepted()) {
            return false;
        }

        if (! $user->hasRole('org-owner') || $user->organization_id === null) {
            return false;
        }

        return (int) $user->organization_id === (int) $invitation->organization_id;
    }

    public function resend(User $user, OrganizationInvitation $invitation): bool
    {
        return $this->delete($user, $invitation);
    }
}
