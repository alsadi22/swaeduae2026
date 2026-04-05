<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationEventRequest;
use App\Http\Requests\Organization\UpdateOrganizationEventRequest;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use App\Support\AttendanceCheckpointUrl;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationEventController extends Controller
{
    private const EVENT_INDEX_TIMING_ALL = 'all';

    private const EVENT_INDEX_TIMING_UPCOMING = 'upcoming';

    private const EVENT_INDEX_TIMING_PAST = 'past';

    private const EVENT_INDEX_SORT_STARTS_DESC = 'starts_desc';

    private const EVENT_INDEX_SORT_STARTS_ASC = 'starts_asc';

    public function index(Request $request): View
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'timing' => ['nullable', 'string', 'in:'.self::EVENT_INDEX_TIMING_ALL.','.self::EVENT_INDEX_TIMING_UPCOMING.','.self::EVENT_INDEX_TIMING_PAST],
            'sort' => ['nullable', 'string', 'in:'.self::EVENT_INDEX_SORT_STARTS_DESC.','.self::EVENT_INDEX_SORT_STARTS_ASC],
        ]);

        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;
        $timing = $validated['timing'] ?? self::EVENT_INDEX_TIMING_ALL;
        if ($timing === '') {
            $timing = self::EVENT_INDEX_TIMING_ALL;
        }
        $sort = $validated['sort'] ?? self::EVENT_INDEX_SORT_STARTS_DESC;
        if ($sort === '') {
            $sort = self::EVENT_INDEX_SORT_STARTS_DESC;
        }

        $query = Event::query()
            ->where('organization_id', $orgId)
            ->withCount(['volunteers', 'attendances']);

        if ($sort === self::EVENT_INDEX_SORT_STARTS_ASC) {
            $query->orderBy('event_starts_at');
        } else {
            $query->orderByDesc('event_starts_at');
        }

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title_en::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(title_ar::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($timing === self::EVENT_INDEX_TIMING_UPCOMING) {
            $query->where('event_ends_at', '>=', now());
        } elseif ($timing === self::EVENT_INDEX_TIMING_PAST) {
            $query->where('event_ends_at', '<', now());
        }

        $events = $query->paginate(15)->appends(PublicLocale::mergeQuery(array_filter([
            'search' => $searchInput,
            'timing' => $timing !== self::EVENT_INDEX_TIMING_ALL ? $timing : null,
            'sort' => $sort !== self::EVENT_INDEX_SORT_STARTS_DESC ? $sort : null,
        ])));

        return view('organization.events.index', [
            'events' => $events,
            'search' => $searchInput,
            'timing' => $timing,
            'sort' => $sort,
        ]);
    }

    public function roster(Request $request, Event $event): View
    {
        $this->authorize('viewInOrganizationPortal', $event);

        [$searchInput, $searchTerm] = $this->parsedRosterSearch($request);

        $volunteers = $this->volunteersForRosterListing($event, $searchTerm)
            ->paginate(30)
            ->appends(PublicLocale::mergeQuery(array_filter([
                'search' => $searchInput,
            ])));

        $userIds = $volunteers->getCollection()->pluck('id')->all();
        $attendanceByUserId = $userIds === []
            ? collect()
            : Attendance::query()
                ->where('event_id', $event->id)
                ->whereIn('user_id', $userIds)
                ->get()
                ->keyBy('user_id');

        return view('organization.events.roster', [
            'event' => $event,
            'volunteers' => $volunteers,
            'attendanceByUserId' => $attendanceByUserId,
            'search' => $searchInput,
        ]);
    }

    public function exportRoster(Request $request, Event $event)
    {
        $this->authorize('viewInOrganizationPortal', $event);

        [, $searchTerm] = $this->parsedRosterSearch($request);

        $volunteers = $this->volunteersForRosterListing($event, $searchTerm)->get();
        $userIds = $volunteers->pluck('id')->all();
        $attendanceByUserId = $userIds === []
            ? collect()
            : Attendance::query()
                ->where('event_id', $event->id)
                ->whereIn('user_id', $userIds)
                ->get()
                ->keyBy('user_id');

        $slug = Str::slug($event->title_en) ?: 'roster';
        $filename = $slug.'-'.$event->uuid.($searchTerm !== null ? '-filtered' : '').'.csv';

        $headers = [
            __('Name'),
            __('Email'),
            __('Joined roster'),
            __('Status'),
            __('Verified minutes'),
        ];

        return response()->streamDownload(function () use ($volunteers, $attendanceByUserId, $headers): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            $tz = config('app.timezone');
            foreach ($volunteers as $v) {
                $att = $attendanceByUserId->get($v->id);
                $joined = $v->pivot?->created_at
                    ? $v->pivot->created_at->timezone($tz)->format('Y-m-d H:i')
                    : '';
                $verified = ($att !== null && $att->state === Attendance::STATE_CHECKED_OUT)
                    ? (string) ($att->verifiedMinutes() ?? '')
                    : '';
                fputcsv($out, [
                    $v->name,
                    $v->email,
                    $joined,
                    $att !== null ? Attendance::localizedStateLabel($att->state) : '',
                    $verified,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(Request $request): View
    {
        $organization = $request->user()->organization;
        $event = new Event;
        $event->organization_id = $organization?->id;

        return view('organization.events.create', [
            'event' => $event,
            'portalOrganization' => $organization,
        ]);
    }

    public function store(StoreOrganizationEventRequest $request): RedirectResponse
    {
        Event::query()->create(array_merge($request->validated(), [
            'organization_id' => $request->user()->organization_id,
        ]));

        return redirect()
            ->route('organization.events.index', PublicLocale::query())
            ->with('status', __('Event created.'));
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorize('configureInOrganizationPortal', $event);

        return view('organization.events.edit', [
            'event' => $event,
            'portalOrganization' => $request->user()->organization,
        ]);
    }

    public function update(UpdateOrganizationEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()
            ->route('organization.events.index', PublicLocale::query())
            ->with('status', __('Event updated.'));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('deleteInOrganizationPortal', $event);

        $event->delete();

        return redirect()
            ->route('organization.events.index', PublicLocale::query())
            ->with('status', __('Event deleted.'));
    }

    public function checkpointSignedUrl(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('configureInOrganizationPortal', $event);

        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $days = (int) ($validated['days'] ?? 7);

        return redirect()
            ->route('organization.events.edit', array_merge(['event' => $event], PublicLocale::query()))
            ->with('checkpoint_signed_url', AttendanceCheckpointUrl::temporarySignedShowUrl($event, $days));
    }

    public function destroyRosterVolunteer(Request $request, Event $event, User $volunteer): RedirectResponse
    {
        $this->authorize('removeVolunteerFromRosterInOrganizationPortal', [$event, $volunteer]);

        $event->volunteers()->detach($volunteer->id);

        $params = array_merge(['event' => $event], PublicLocale::query());
        if ($request->filled('search')) {
            $params['search'] = $request->string('search')->toString();
        }

        return redirect()
            ->route('organization.events.roster', $params)
            ->with('status', __('Volunteer removed from roster.'));
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function parsedRosterSearch(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [$searchInput, $searchTerm];
    }

    /**
     * @return BelongsToMany<User, Event>
     */
    private function volunteersForRosterListing(Event $event, ?string $searchTerm): BelongsToMany
    {
        $relation = $event->volunteers()->orderBy('users.name');
        if ($searchTerm !== null) {
            $relation->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(users.name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(users.email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        return $relation;
    }
}
