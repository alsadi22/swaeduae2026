<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function approve(User $user, Organization $organization): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin'])
            && $organization->isPendingVerification();
    }

    public function reject(User $user, Organization $organization): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin'])
            && $organization->isPendingVerification();
    }
}
