<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\CarbonImmutable;

class AttendanceJournal
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function append(Attendance $attendance, string $action, ?int $actorUserId, array $payload = []): void
    {
        AttendanceLog::query()->create([
            'attendance_id' => $attendance->id,
            'action' => $action,
            'actor_user_id' => $actorUserId,
            'payload' => $payload === [] ? null : $payload,
            'created_at' => CarbonImmutable::now(),
        ]);
    }
}
