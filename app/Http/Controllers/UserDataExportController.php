<?php

namespace App\Http\Controllers;

use App\Models\CheckinAttempt;
use App\Models\Dispute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase D6 (PRD): portable JSON export of the authenticated account (no passwords or GPS).
 */
class UserDataExportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing([
            'volunteerProfile',
            'organization',
            'eventApplications.event',
            'attendances.event',
        ]);

        $disputes = Dispute::query()
            ->whereHas('attendance', fn ($q) => $q->where('user_id', $user->id))
            ->with(['attendance.event'])
            ->orderByDesc('created_at')
            ->get();

        $checkinAttemptsSummary = CheckinAttempt::query()
            ->where('user_id', $user->id)
            ->get(['outcome'])
            ->countBy(fn ($row) => $row->outcome ?? 'unknown')
            ->all();

        $payload = [
            'schema' => 'swaeduae.account_export',
            'schema_version' => 3,
            'exported_at' => now()->toIso8601String(),
            'note' => 'Coordinates and internal audit logs are not included. Contact support for a fuller subject-access package if required.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'locale_preferred' => $user->locale_preferred,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'terms_accepted_at' => $user->terms_accepted_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
                'roles' => $user->getRoleNames()->values()->all(),
            ],
            'organization_membership' => $user->organization === null ? null : [
                'organization_id' => $user->organization->id,
                'name_en' => $user->organization->name_en,
                'name_ar' => $user->organization->name_ar,
                'verification_status' => $user->organization->verification_status,
            ],
            'volunteer_profile' => $user->volunteerProfile === null ? null : [
                'bio' => $user->volunteerProfile->bio,
                'skills' => $user->volunteerProfile->skills,
                'availability' => $user->volunteerProfile->availability,
                'emergency_contact_name' => $user->volunteerProfile->emergency_contact_name,
                'emergency_contact_phone' => $user->volunteerProfile->emergency_contact_phone,
                'emirates_id_masked' => $user->volunteerProfile->emirates_id_masked,
                'notification_email_opt_in' => $user->volunteerProfile->notification_email_opt_in,
                'updated_at' => $user->volunteerProfile->updated_at?->toIso8601String(),
            ],
            'event_applications' => $user->eventApplications->map(static function ($app): array {
                return [
                    'status' => $app->status,
                    'message' => $app->message,
                    'review_note' => $app->review_note,
                    'created_at' => $app->created_at?->toIso8601String(),
                    'updated_at' => $app->updated_at?->toIso8601String(),
                    'event' => $app->event === null ? null : [
                        'uuid' => $app->event->uuid,
                        'title_en' => $app->event->title_en,
                        'title_ar' => $app->event->title_ar,
                    ],
                ];
            })->values()->all(),
            'attendances' => $user->attendances->map(static function ($row): array {
                return [
                    'state' => $row->state,
                    'checked_in_at' => $row->checked_in_at?->toIso8601String(),
                    'checked_out_at' => $row->checked_out_at?->toIso8601String(),
                    'minutes_worked' => $row->minutes_worked,
                    'minutes_adjustment' => $row->minutes_adjustment,
                    'minutes_adjustment_note' => $row->minutes_adjustment_note,
                    'verified_minutes' => $row->verifiedMinutes(),
                    'suspicion_flags' => $row->suspicion_flags ?? [],
                    'event' => $row->event === null ? null : [
                        'uuid' => $row->event->uuid,
                        'title_en' => $row->event->title_en,
                    ],
                ];
            })->values()->all(),
            'disputes' => $disputes->map(static function (Dispute $d): array {
                return [
                    'id' => $d->id,
                    'status' => $d->status,
                    'description' => $d->description,
                    'resolution_note' => $d->resolution_note,
                    'created_at' => $d->created_at?->toIso8601String(),
                    'updated_at' => $d->updated_at?->toIso8601String(),
                    'resolved_at' => $d->resolved_at?->toIso8601String(),
                    'event' => $d->attendance?->event === null ? null : [
                        'uuid' => $d->attendance->event->uuid,
                        'title_en' => $d->attendance->event->title_en,
                    ],
                ];
            })->values()->all(),
            'checkin_attempts_summary' => $checkinAttemptsSummary,
        ];

        $filename = 'swaeduae-account-data-'.$user->id.'-'.now()->format('Y-m-d').'.json';

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
