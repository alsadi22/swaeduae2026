<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Models\User;
use App\Support\Geo\Haversine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AttendanceCheckpointService
{
    public function __construct(
        private readonly AttendanceJournal $journal,
        private readonly int $rapidAttemptThreshold = 5,
        private readonly int $rapidAttemptWindowSeconds = 60,
    ) {}

    /**
     * @return array{ok: bool, message: string, attendance: ?Attendance, suspicion_flags: array<int, string>}
     */
    public function process(
        User $user,
        Event $event,
        string $action,
        ?float $latitude,
        ?float $longitude,
        ?float $accuracyMeters,
        string $ipAddress,
    ): array {
        if (! in_array($action, [CheckinAttempt::TYPE_CHECK_IN, CheckinAttempt::TYPE_CHECK_OUT], true)) {
            return $this->failure(__('Invalid action.'), $event, $user, $action, $latitude, $longitude, $accuracyMeters, $ipAddress, 'invalid_action');
        }

        if (! $event->userIsOnRoster($user)) {
            return $this->failure(__('You are not registered for this event.'), $event, $user, $action, $latitude, $longitude, $accuracyMeters, $ipAddress, 'not_on_roster');
        }

        if ($latitude === null || $longitude === null) {
            return $this->failure(__('Location is required for attendance.'), $event, $user, $action, $latitude, $longitude, $accuracyMeters, $ipAddress, 'missing_coordinates');
        }

        $now = CarbonImmutable::now();

        $distance = Haversine::meters(
            (float) $event->latitude,
            (float) $event->longitude,
            $latitude,
            $longitude
        );

        $flags = [];
        $rejectionReason = null;
        $outcome = $action === CheckinAttempt::TYPE_CHECK_IN ? 'accepted' : 'accepted';

        if ($accuracyMeters !== null && $event->min_gps_accuracy_meters !== null && $accuracyMeters > $event->min_gps_accuracy_meters) {
            $flags[] = 'low_gps_accuracy';
            if ($event->geofence_strict) {
                $rejectionReason = 'low_gps_accuracy';
            }
        }

        if ($distance > $event->geofence_radius_meters) {
            $flags[] = 'outside_geofence';
            if ($event->geofence_strict) {
                $rejectionReason = 'outside_geofence';
            }
        }

        if ($action === CheckinAttempt::TYPE_CHECK_IN) {
            if ($now->lt($event->checkin_window_starts_at) || $now->gt($event->checkin_window_ends_at)) {
                $flags[] = 'outside_checkin_window';
                $rejectionReason ??= 'outside_checkin_window';
            }
        }

        if ($this->tooManyRecentAttempts($event, $user)) {
            $flags[] = 'rapid_repeated_attempts';
            $rejectionReason ??= 'rapid_repeated_attempts';
        }

        if ($rejectionReason !== null) {
            $this->logAttempt(
                $event,
                $user,
                $action,
                $latitude,
                $longitude,
                $accuracyMeters,
                $distance,
                'rejected',
                $rejectionReason,
                $flags,
                $ipAddress
            );

            return [
                'ok' => false,
                'message' => __('Check-in was rejected. Reason: :reason', ['reason' => $rejectionReason]),
                'attendance' => null,
                'suspicion_flags' => $flags,
            ];
        }

        return DB::transaction(function () use ($user, $event, $action, $latitude, $longitude, $accuracyMeters, $distance, $flags, $now, $ipAddress) {
            $attendance = Attendance::query()->firstOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ],
                ['state' => Attendance::STATE_PENDING]
            );

            if ($action === CheckinAttempt::TYPE_CHECK_IN) {
                if ($attendance->state === Attendance::STATE_CHECKED_IN) {
                    $this->logAttempt(
                        $event,
                        $user,
                        $action,
                        $latitude,
                        $longitude,
                        $accuracyMeters,
                        $distance,
                        'rejected',
                        'already_checked_in',
                        $flags,
                        $ipAddress
                    );

                    return [
                        'ok' => false,
                        'message' => __('You are already checked in.'),
                        'attendance' => $attendance->fresh(),
                        'suspicion_flags' => $flags,
                    ];
                }

                $attendance->update([
                    'state' => Attendance::STATE_CHECKED_IN,
                    'checked_in_at' => $now,
                    'check_in_latitude' => $latitude,
                    'check_in_longitude' => $longitude,
                    'check_in_accuracy_meters' => $accuracyMeters,
                    'suspicion_flags' => $this->mergeFlags($attendance->suspicion_flags ?? [], $flags),
                ]);

                $this->journal->append($attendance, 'check_in', $user->id, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_meters' => $accuracyMeters,
                    'distance_meters' => $distance,
                    'flags' => $flags,
                ]);
            } else {
                if ($attendance->state !== Attendance::STATE_CHECKED_IN) {
                    $this->logAttempt(
                        $event,
                        $user,
                        $action,
                        $latitude,
                        $longitude,
                        $accuracyMeters,
                        $distance,
                        'rejected',
                        'not_checked_in',
                        $flags,
                        $ipAddress
                    );

                    return [
                        'ok' => false,
                        'message' => __('You must check in before checking out.'),
                        'attendance' => $attendance->fresh(),
                        'suspicion_flags' => $flags,
                    ];
                }

                $checkoutUntil = $event->event_ends_at->copy()->addMinutes($event->checkout_grace_minutes_after_event);
                if ($now->gt($checkoutUntil)) {
                    $this->logAttempt(
                        $event,
                        $user,
                        $action,
                        $latitude,
                        $longitude,
                        $accuracyMeters,
                        $distance,
                        'rejected',
                        'checkout_window_closed',
                        $flags,
                        $ipAddress
                    );

                    return [
                        'ok' => false,
                        'message' => __('The checkout window for this event has closed.'),
                        'attendance' => $attendance->fresh(),
                        'suspicion_flags' => $flags,
                    ];
                }

                $minutesWorked = (int) max(0, $attendance->checked_in_at->diffInMinutes($now));

                $attendance->update([
                    'state' => Attendance::STATE_CHECKED_OUT,
                    'checked_out_at' => $now,
                    'check_out_latitude' => $latitude,
                    'check_out_longitude' => $longitude,
                    'check_out_accuracy_meters' => $accuracyMeters,
                    'suspicion_flags' => $this->mergeFlags($attendance->suspicion_flags ?? [], $flags),
                    'minutes_worked' => $minutesWorked,
                ]);

                $this->journal->append($attendance, 'check_out', $user->id, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_meters' => $accuracyMeters,
                    'distance_meters' => $distance,
                    'flags' => $flags,
                    'minutes_worked' => $minutesWorked,
                ]);
            }

            $this->logAttempt(
                $event,
                $user,
                $action,
                $latitude,
                $longitude,
                $accuracyMeters,
                $distance,
                count($flags) > 0 ? 'suspicious' : 'accepted',
                null,
                $flags,
                $ipAddress
            );

            return [
                'ok' => true,
                'message' => $action === CheckinAttempt::TYPE_CHECK_IN
                    ? __('Checked in successfully.')
                    : __('Checked out successfully.'),
                'attendance' => $attendance->fresh(),
                'suspicion_flags' => $flags,
            ];
        });
    }

    /**
     * @param  array<int, string>  $flags
     * @return array{ok: false, message: string, attendance: null, suspicion_flags: array<int, string>}
     */
    private function failure(
        string $message,
        Event $event,
        User $user,
        string $action,
        ?float $latitude,
        ?float $longitude,
        ?float $accuracyMeters,
        string $ipAddress,
        string $reason,
    ): array {
        $distance = null;
        if ($latitude !== null && $longitude !== null) {
            $distance = Haversine::meters(
                (float) $event->latitude,
                (float) $event->longitude,
                $latitude,
                $longitude
            );
        }

        $this->logAttempt(
            $event,
            $user,
            $action,
            $latitude,
            $longitude,
            $accuracyMeters,
            $distance,
            'rejected',
            $reason,
            [],
            $ipAddress
        );

        return [
            'ok' => false,
            'message' => $message,
            'attendance' => null,
            'suspicion_flags' => [],
        ];
    }

    private function tooManyRecentAttempts(Event $event, User $user): bool
    {
        $count = CheckinAttempt::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subSeconds($this->rapidAttemptWindowSeconds))
            ->count();

        return $count >= $this->rapidAttemptThreshold;
    }

    /**
     * @param  array<int, string>  $flags
     */
    private function logAttempt(
        Event $event,
        User $user,
        string $action,
        ?float $latitude,
        ?float $longitude,
        ?float $accuracyMeters,
        ?float $distance,
        string $outcome,
        ?string $rejectionReason,
        array $flags,
        string $ipAddress,
    ): void {
        CheckinAttempt::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'attempt_type' => $action,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => $accuracyMeters,
            'distance_meters' => $distance,
            'outcome' => $outcome,
            'rejection_reason' => $rejectionReason,
            'flags' => $flags ?: null,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<int, string>  $existing
     * @param  array<int, string>  $new
     * @return array<int, string>
     */
    private function mergeFlags(array $existing, array $new): array
    {
        return array_values(array_unique([...$existing, ...$new]));
    }
}
