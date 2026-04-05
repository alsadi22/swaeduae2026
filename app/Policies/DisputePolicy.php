<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, Dispute $dispute): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        if ($user->hasRole('volunteer')) {
            return $dispute->opened_by_user_id === $user->id
                || $dispute->attendance?->user_id === $user->id;
        }

        return false;
    }

    public function resolve(User $user, Dispute $dispute): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) && $dispute->isOpen();
    }
}
