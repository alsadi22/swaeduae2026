<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventStoreRequest;
use App\Http\Requests\Admin\EventUpdateRequest;
use App\Models\Event;
use App\Models\Organization;
use App\Support\AttendanceCheckpointUrl;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        $query = Event::query()
            ->with('organization')
            ->withCount('volunteers');

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title_en::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(title_ar::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereHas('organization', function ($oq) use ($searchTerm): void {
                        $oq->whereRaw('strpos(lower(name_en::text), lower(?::text)) > 0', [$searchTerm])
                            ->orWhereRaw('strpos(lower(name_ar::text), lower(?::text)) > 0', [$searchTerm]);
                    });
            });
        }

        $events = $query->orderByDesc('event_starts_at')->paginate(15)->withQueryString()->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('admin.events.index', [
            'events' => $events,
            'search' => $searchInput,
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
}
