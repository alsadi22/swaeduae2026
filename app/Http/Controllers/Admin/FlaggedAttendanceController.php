<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CheckinAttempt;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlaggedAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $validated = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $eventId = $validated['event_id'] ?? null;
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        $query = Attendance::query()
            ->withNonEmptySuspicionFlags()
            ->with(['event.organization', 'user'])
            ->orderByDesc('updated_at');

        if ($eventId !== null) {
            $query->where('event_id', $eventId);
        }

        if ($searchTerm !== null) {
            $query->whereHas('user', function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        $rows = $query->paginate(25)->withQueryString();

        $filterEvents = Event::query()
            ->whereIn(
                'id',
                Attendance::query()
                    ->select('event_id')
                    ->withNonEmptySuspicionFlags()
                    ->distinct()
            )
            ->orderBy('title_en')
            ->get();

        return view('admin.flagged-attendance.index', [
            'rows' => $rows,
            'filterEvents' => $filterEvents,
            'eventId' => $eventId,
            'search' => $searchInput,
        ]);
    }
}
