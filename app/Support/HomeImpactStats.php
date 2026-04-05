<?php

namespace App\Support;

use App\Models\Organization;

final class HomeImpactStats
{
    /**
     * @return array{
     *     verified_minutes_total: int,
     *     hours_display: string,
     *     volunteers_count: int,
     *     partners_count: int,
     *     events_with_verified_time_count: int,
     * }
     */
    public static function snapshot(): array
    {
        $verifiedMinutesTotal = VerifiedAttendanceMinutes::globalTotal();

        $hoursRounded = (int) max(0, round($verifiedMinutesTotal / 60));
        $hoursDisplay = number_format($hoursRounded);

        $volunteersCount = VerifiedAttendanceMinutes::distinctVolunteerCount();

        $partnersCount = (int) Organization::query()
            ->where('verification_status', Organization::VERIFICATION_APPROVED)
            ->count();

        $eventsWithVerifiedTimeCount = VerifiedAttendanceMinutes::distinctEventCount();

        return [
            'verified_minutes_total' => $verifiedMinutesTotal,
            'hours_display' => $hoursDisplay,
            'volunteers_count' => $volunteersCount,
            'partners_count' => $partnersCount,
            'events_with_verified_time_count' => $eventsWithVerifiedTimeCount,
        ];
    }
}
