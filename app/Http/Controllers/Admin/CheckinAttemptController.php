<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckinAttemptController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $validated = $request->validate([
            'outcome' => ['nullable', 'string', Rule::in(['all', 'accepted', 'suspicious', 'rejected'])],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $outcomeFilter = $validated['outcome'] ?? 'all';
        $eventId = $validated['event_id'] ?? null;
        $search = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $search === '' ? null : $search;

        $query = CheckinAttempt::query()
            ->with(['event.organization', 'user'])
            ->orderByDesc('created_at');

        if ($outcomeFilter !== 'all') {
            $query->where('outcome', $outcomeFilter);
        }

        if ($eventId !== null) {
            $query->where('event_id', $eventId);
        }

        if ($searchTerm !== null) {
            $query->whereHas('user', function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        $attempts = $query->paginate(40)->withQueryString()->appends(PublicLocale::query());

        $filterEvents = Event::query()
            ->whereIn('id', CheckinAttempt::query()->select('event_id')->distinct())
            ->orderBy('title_en')
            ->get();

        return view('admin.checkin-attempts.index', [
            'attempts' => $attempts,
            'filterEvents' => $filterEvents,
            'outcomeFilter' => $outcomeFilter,
            'eventId' => $eventId,
            'search' => $search,
        ]);
    }
}
