<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventStoreRequest;
use App\Http\Requests\Admin\EventUpdateRequest;
use App\Models\Event;
use App\Models\Organization;
use App\Support\AttendanceCheckpointUrl;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $state = $this->validatedEventListFilters($request);

        $events = $this->eventsListQuery($state)
            ->orderByDesc('event_starts_at')
            ->paginate(15)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('admin.events.index', [
            'events' => $events,
            'search' => $state['search_input'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Event::class);

        $state = $this->validatedEventListFilters($request);

        $rows = $this->eventsListQuery($state)
            ->orderByDesc('event_starts_at')
            ->get();

        $filtered = $state['search_term'] !== null;
        $filename = 'events-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('Title (English)'),
                __('Title (Arabic)'),
                __('Organization'),
                __('Event starts'),
                __('Event ends'),
                __('Capacity'),
                __('Application required'),
                __('Roster'),
            ]);
            foreach ($rows as $e) {
                fputcsv($out, [
                    (string) $e->id,
                    $e->title_en,
                    $e->title_ar ?? '',
                    $e->organization?->name_en ?? '',
                    $e->event_starts_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $e->event_ends_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $e->capacity !== null ? (string) $e->capacity : '',
                    $e->application_required ? '1' : '0',
                    (string) ($e->volunteers_count ?? 0),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $organizations = Organization::query()->orderBy('name_en')->get();

        return view('admin.events.create', [
            'event' => new Event,
            'organizations' => $organizations,
        ]);
    }

    public function store(EventStoreRequest $request): RedirectResponse
    {
        Event::query()->create($request->validated());

        return redirect()
            ->route('admin.events.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Event created.'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $organizations = Organization::query()->orderBy('name_en')->get();

        return view('admin.events.edit', compact('event', 'organizations'));
    }

    public function update(EventUpdateRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()
            ->route('admin.events.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Event updated.'));
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('admin.events.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Event deleted.'));
    }

    public function checkpointSignedUrl(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $days = (int) ($validated['days'] ?? 7);

        return redirect()
            ->route('admin.events.edit', array_merge(['event' => $event], PublicLocale::queryFromRequestOrUser($request->user())))
            ->with('checkpoint_signed_url', AttendanceCheckpointUrl::temporarySignedShowUrl($event, $days));
    }

    /**
     * @return array{search_input: string, search_term: string|null}
     */
    private function validatedEventListFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
        ];
    }

    /**
     * @param  array{search_term: string|null}  $state
     * @return Builder<Event>
     */
    private function eventsListQuery(array $state): Builder
    {
        $query = Event::query()
            ->with('organization')
            ->withCount('volunteers');

        if ($state['search_term'] !== null) {
            $searchTerm = $state['search_term'];
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title_en::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(title_ar::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereHas('organization', function ($oq) use ($searchTerm): void {
                        $oq->whereRaw('strpos(lower(name_en::text), lower(?::text)) > 0', [$searchTerm])
                            ->orWhereRaw('strpos(lower(name_ar::text), lower(?::text)) > 0', [$searchTerm]);
                    });
            });
        }

        return $query;
    }
}
