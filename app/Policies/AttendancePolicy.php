<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function dispute(User $user, Attendance $attendance): bool
    {
        if (! $user->hasRole('volunteer') || $attendance->user_id !== $user->id) {
            return false;
        }

        if ($attendance->state !== Attendance::STATE_CHECKED_OUT) {
            return false;
        }

        return ! $attendance->hasOpenDispute();
    }

    public function adjustMinutes(User $user, Attendance $attendance): bool
    {
        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            return false;
        }

        if ($attendance->state !== Attendance::STATE_CHECKED_OUT || $attendance->minutes_worked === null) {
            return false;
        }

        return true;
    }
}
