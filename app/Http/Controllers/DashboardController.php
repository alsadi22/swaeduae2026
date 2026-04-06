<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EventApplication;
use App\Support\PublicLocale;
use App\Support\VerifiedAttendanceMinutes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $verifiedVolunteerMinutesTotal = 0;
        $verifiedVolunteerHoursRounded = 0;
        $verifiedVolunteerSessionsCount = 0;

        $validated = $request->validate([
            'application_status' => [
                'nullable',
                'string',
                Rule::in([
                    'all',
                    EventApplication::STATUS_PENDING,
                    EventApplication::STATUS_APPROVED,
                    EventApplication::STATUS_REJECTED,
                    EventApplication::STATUS_WITHDRAWN,
                ]),
            ],
            'past_page' => ['nullable', 'integer', 'min:1'],
            'upcoming_page' => ['nullable', 'integer', 'min:1'],
            'app_page' => ['nullable', 'integer', 'min:1'],
        ]);
        $applicationStatusFilter = $validated['application_status'] ?? 'all';

        $savedOpportunityEvents = collect();

        if ($user->hasRole('volunteer')) {
            $savedOpportunityEvents = $user
                ->savedEvents()
                ->with('organization')
                ->where('event_ends_at', '>=', now())
                ->orderBy('event_starts_at')
                ->take(12)
                ->get();

            $upcomingRosterEvents = $user
                ->rosteredEvents()
                ->with('organization')
                ->where('event_ends_at', '>=', now())
                ->orderBy('event_starts_at')
                ->paginate(5, ['*'], 'upcoming_page')
                ->withQueryString()
                ->appends(PublicLocale::queryFromRequestOrUser($user));

            $pastRosterEvents = $user
                ->rosteredEvents()
                ->with('organization')
                ->where('event_ends_at', '<', now())
                ->orderByDesc('event_ends_at')
                ->paginate(5, ['*'], 'past_page')
                ->withQueryString()
                ->appends(PublicLocale::queryFromRequestOrUser($user));

            $applicationsQuery = $user
                ->eventApplications()
                ->with(['event.organization'])
                ->orderByDesc('updated_at');

            if ($applicationStatusFilter !== 'all') {
                $applicationsQuery->where('status', $applicationStatusFilter);
            }

            $myApplications = $applicationsQuery
                ->paginate(10, ['*'], 'app_page')
                ->withQueryString()
                ->appends(PublicLocale::queryFromRequestOrUser($user));

            $verifiedVolunteerMinutesTotal = VerifiedAttendanceMinutes::totalForUser($user);

            $verifiedVolunteerHoursRounded = (int) max(0, round($verifiedVolunteerMinutesTotal / 60));

            $verifiedVolunteerSessionsCount = (int) $user
                ->attendances()
                ->where('state', Attendance::STATE_CHECKED_OUT)
                ->whereNotNull('minutes_worked')
                ->count();
        } else {
            $query = array_merge($request->query(), PublicLocale::queryFromRequestOrUser($user));
            $upcomingRosterEvents = new LengthAwarePaginator([], 0, 5, 1, [
                'path' => $request->url(),
                'pageName' => 'upcoming_page',
                'query' => $query,
            ]);
            $pastRosterEvents = new LengthAwarePaginator([], 0, 5, 1, [
                'path' => $request->url(),
                'pageName' => 'past_page',
                'query' => $query,
            ]);
            $myApplications = new LengthAwarePaginator([], 0, 10, 1, [
                'path' => $request->url(),
                'pageName' => 'app_page',
                'query' => $query,
            ]);
        }

        $volunteerProfileCompleteForCommitments = ! $user->hasRole('volunteer')
            || $user->hasMinimumVolunteerProfileForCommitments();

        return view('dashboard', compact(
            'upcomingRosterEvents',
            'pastRosterEvents',
            'myApplications',
            'applicationStatusFilter',
            'verifiedVolunteerMinutesTotal',
            'verifiedVolunteerHoursRounded',
            'verifiedVolunteerSessionsCount',
            'volunteerProfileCompleteForCommitments',
            'savedOpportunityEvents',
        ));
    }
}
