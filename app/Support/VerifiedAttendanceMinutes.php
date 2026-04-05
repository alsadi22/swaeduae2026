<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Verified volunteer minutes = checked-out sessions with minutes_worked,
 * plus optional admin adjustment (clamped to non-negative per session).
 */
final class VerifiedAttendanceMinutes
{
    private const SUM_VERIFIED_MINUTES = 'CASE WHEN (minutes_worked + COALESCE(minutes_adjustment, 0)) < 0 THEN 0 ELSE (minutes_worked + COALESCE(minutes_adjustment, 0)) END';

    public static function baseQuery(): Builder
    {
        return Attendance::query()
            ->where('state', Attendance::STATE_CHECKED_OUT)
            ->whereNotNull('minutes_worked');
    }

    public static function totalForUser(User $user): int
    {
        return (int) self::baseQuery()
            ->where('user_id', $user->id)
            ->sum(DB::raw(self::SUM_VERIFIED_MINUTES));
    }

    public static function globalTotal(): int
    {
        return (int) self::baseQuery()
            ->sum(DB::raw(self::SUM_VERIFIED_MINUTES));
    }

    public static function distinctVolunteerCount(): int
    {
        return (int) self::baseQuery()->distinct()->count('user_id');
    }

    public static function distinctEventCount(): int
    {
        return (int) self::baseQuery()->distinct()->count('event_id');
    }
}
