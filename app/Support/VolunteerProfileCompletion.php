<?php

namespace App\Support;

use App\Models\VolunteerProfile;

final class VolunteerProfileCompletion
{
    /**
     * Weighted 0–100 score: bio, emergency contacts, optional skills/availability.
     */
    public static function percent(VolunteerProfile $profile): int
    {
        $bioLen = strlen(trim((string) $profile->bio));
        $bioScore = (int) min(35, (int) round($bioLen / 20 * 35));

        $score = $bioScore;
        if (filled($profile->emergency_contact_name)) {
            $score += 22;
        }
        if (filled($profile->emergency_contact_phone)) {
            $score += 22;
        }
        if (filled(trim((string) $profile->skills))) {
            $score += 11;
        }
        if (filled(trim((string) $profile->availability))) {
            $score += 10;
        }

        return min(100, $score);
    }
}
