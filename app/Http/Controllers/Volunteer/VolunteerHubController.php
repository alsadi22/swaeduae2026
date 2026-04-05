<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Volunteer\ApplyToOpportunityRequest;
use App\Models\Event;
use App\Models\EventApplication;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class VolunteerHubController extends Controller
{
    public function index(): View
    {
        return view('volunteer.index');
    }

    public function opportunities(Request $request): View
    {
        $validated = $request->validate([
            'sort' => ['nullable', 'string', 'in:starts_soon,starts_late'],
            'entry' => ['nullable', 'string', 'in:all,open,application'],
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $sort = $validated['sort'] ?? 'starts_soon';
        $entry = $validated['entry'] ?? 'all';
        $searchInput = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $search = $searchInput === '' ? '' : mb_substr($searchInput, 0, 120);

        $query = Event::query()
            ->with('organization')
            ->withCount('volunteers')
            ->where('event_ends_at', '>=', now());

        if ($entry === 'open') {
            $query->where('application_required', false);
        } elseif ($entry === 'application') {
            $query->where('application_required', true);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where(function ($qq) use ($search): void {
                    $qq->whereRaw('strpos(lower(title_en::text), lower(?::text)) > 0', [$search])
                        ->orWhereRaw('strpos(lower(title_ar::text), lower(?::text)) > 0', [$search]);
                })->orWhereHas('organization', function ($qq) use ($search): void {
                    $qq->whereRaw('strpos(lower(name_en::text), lower(?::text)) > 0', [$search])
                        ->orWhereRaw('strpos(lower(name_ar::text), lower(?::text)) > 0', [$search]);
                });
            });
        }

        if ($sort === 'starts_late') {
            $query->orderByDesc('event_starts_at');
        } else {
            $query->orderBy('event_starts_at');
        }

        $events = $query->paginate(15)->withQueryString()->appends(PublicLocale::query());

        return view('volunteer.opportunities.index', compact('events', 'search', 'sort', 'entry'));
    }

    public function showOpportunity(Request $request, Event $event): View
    {
        $event->load('organization');
        $event->loadCount('volunteers');

        $application = null;
        $volunteerProfileCompleteForCommitments = true;
        $pendingApplicationsOnOtherEventsCount = 0;
        if ($request->user()?->hasRole('volunteer')) {
            $user = $request->user();
            $application = $event->applicationForUser($user);
            $volunteerProfileCompleteForCommitments = $user->hasMinimumVolunteerProfileForCommitments();
            $pendingApplicationsOnOtherEventsCount = EventApplication::query()
                ->where('user_id', $user->id)
                ->where('status', EventApplication::STATUS_PENDING)
                ->where('event_id', '!=', $event->id)
                ->count();
        }

        return view('volunteer.opportunities.show', compact(
            'event',
            'application',
            'volunteerProfileCompleteForCommitments',
            'pendingApplicationsOnOtherEventsCount',
        ));
    }

    /**
     * Redirect to a time-limited signed URL for the attendance checkpoint (opens in same browser; shareable until expiry).
     */
    public function redirectToAttendanceCheckpoint(Event $event): RedirectResponse
    {
        $this->authorize('accessAttendanceCheckpoint', $event);

        $url = URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addDays(7),
            ['event' => $event->uuid]
        );

        return redirect()->to($url);
    }

    public function joinOpportunity(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('joinRoster', $event);

        $user = $request->user();
        $already = $event->userIsOnRoster($user);
        $event->volunteers()->syncWithoutDetaching([$user->id]);

        return redirect()
            ->route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($user)))
            ->with(
                'status',
                $already
                    ? __('You were already on the roster for this opportunity.')
                    : __('You joined this opportunity. Use check-in from your coordinator when the window opens.')
            );
    }

    public function leaveOpportunity(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('leaveRoster', $event);

        $event->volunteers()->detach($request->user()->id);

        return redirect()
            ->route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($request->user())))
            ->with('status', __('You are no longer on the roster for this opportunity.'));
    }

    public function applyToOpportunity(ApplyToOpportunityRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        EventApplication::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => EventApplication::STATUS_PENDING,
                'message' => $data['message'] ?? null,
                'review_note' => null,
            ]
        );

        return redirect()
            ->route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($request->user())))
            ->with('status', __('Application submitted.'));
    }

    public function withdrawApplication(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('withdrawApplication', $event);

        $application = $event->applicationForUser($request->user());
        if ($application?->isPending()) {
            $application->update(['status' => EventApplication::STATUS_WITHDRAWN]);
        }

        return redirect()
            ->route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($request->user())))
            ->with('status', __('Application withdrawn.'));
    }
}
