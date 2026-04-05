<?php

namespace App\Support;

use App\Http\Controllers\Attendance\AttendanceCheckpointController;
use App\Models\Event;
use Illuminate\Support\Facades\URL;

/**
 * Signed GET URL for {@see AttendanceCheckpointController::show}.
 * Coordinators embed this in QR codes or share via messaging; rostered volunteers can also open
 * the same route via {@see route('volunteer.opportunities.attendance')}.
 */
final class AttendanceCheckpointUrl
{
    public static function temporarySignedShowUrl(Event $event, int $daysValid = 7): string
    {
        $days = max(1, $daysValid);

        return URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addDays($days),
            ['event' => $event->uuid]
        );
    }
}
