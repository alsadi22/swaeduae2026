<?php

namespace App\Policies;

use App\Models\User;

class CheckinAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
