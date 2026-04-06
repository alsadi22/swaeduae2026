<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DisputeResolvedVolunteerMail;
use App\Models\Dispute;
use App\Models\Event;
use App\Services\Attendance\AttendanceJournal;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(
        private readonly AttendanceJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Dispute::class);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['all', Dispute::STATUS_OPEN, Dispute::STATUS_RESOLVED, Dispute::STATUS_DISMISSED])],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $statusFilter = $validated['status'] ?? 'all';
        $eventId = $validated['event_id'] ?? null;
        $search = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $search === '' ? null : $search;

        $query = Dispute::query()
            ->with(['attendance.event.organization', 'attendance.user', 'openedBy']);

        if ($eventId !== null) {
            $query->whereHas('attendance', fn ($q) => $q->where('event_id', $eventId));
        }

        if ($searchTerm !== null) {
            $query->whereHas('attendance.user', function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($statusFilter === 'all') {
            $query->orderByRaw('CASE status WHEN ? THEN 0 ELSE 1 END', [Dispute::STATUS_OPEN]);
        } else {
            $query->orderByDesc('updated_at');
        }

        $disputes = $query->paginate(25)->withQueryString()->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $filterEvents = Event::query()
            ->whereIn(
                'id',
                DB::table('disputes')
                    ->join('attendances', 'disputes.attendance_id', '=', 'attendances.id')
                    ->whereNotNull('attendances.event_id')
                    ->select('attendances.event_id')
                    ->distinct()
            )
            ->orderBy('title_en')
            ->get();

        return view('admin.disputes.index', [
            'disputes' => $disputes,
            'statusFilter' => $statusFilter,
            'filterEvents' => $filterEvents,
            'eventId' => $eventId,
            'search' => $search,
        ]);
    }

    public function show(Dispute $dispute): View
    {
        $this->authorize('view', $dispute);

        $dispute->load(['attendance.event.organization', 'attendance.user', 'openedBy', 'resolvedBy']);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorize('resolve', $dispute);

        $validated = $request->validate([
            'resolution' => ['required', 'string', Rule::in([Dispute::STATUS_RESOLVED, Dispute::STATUS_DISMISSED])],
            'resolution_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $dispute->update([
            'status' => $validated['resolution'],
            'resolved_at' => now(),
            'resolved_by_user_id' => $request->user()->id,
            'resolution_note' => $validated['resolution_note'] ?? null,
        ]);

        $dispute->loadMissing(['attendance.user']);
        if ($dispute->attendance) {
            $this->journal->append($dispute->attendance, 'dispute_resolved', $request->user()->id, [
                'dispute_id' => $dispute->id,
                'resolution' => $validated['resolution'],
                'had_resolution_note' => filled($validated['resolution_note'] ?? null),
            ]);
        }

        $volunteer = $dispute->attendance?->user;
        if ($volunteer !== null && filled($volunteer->email)) {
            Mail::to($volunteer->email)->queue(new DisputeResolvedVolunteerMail($dispute->fresh()));
        }

        return redirect()
            ->route('admin.disputes.show', array_merge(['dispute' => $dispute], PublicLocale::queryFromRequestOrUser($request->user())))
            ->with('status', __('Dispute updated.'));
    }
}
