<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DisputeResolvedVolunteerMail;
use App\Models\Dispute;
use App\Models\Event;
use App\Services\Attendance\AttendanceJournal;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisputeController extends Controller
{
    public function __construct(
        private readonly AttendanceJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Dispute::class);

        $state = $this->validatedDisputeListFilters($request);

        $disputes = $this->disputesListQuery($state)
            ->paginate(25)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

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
            'statusFilter' => $state['status_filter'],
            'filterEvents' => $filterEvents,
            'eventId' => $state['event_id'],
            'search' => $state['search_input'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Dispute::class);

        $state = $this->validatedDisputeListFilters($request);

        $rows = $this->disputesListQuery($state)->get();

        $filtered = $state['status_filter'] !== 'all'
            || $state['event_id'] !== null
            || $state['search_term'] !== null;
        $filename = 'disputes-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('Status'),
                __('Created at'),
                __('Updated'),
                __('Event'),
                __('Volunteer name'),
                __('Email'),
                __('Description'),
                __('Opened by'),
                __('Resolved at'),
                __('Resolution note'),
            ]);
            foreach ($rows as $d) {
                $att = $d->attendance;
                $vol = $att?->user;
                fputcsv($out, [
                    (string) $d->id,
                    $d->status,
                    $d->created_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $d->updated_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $att?->event?->title_en ?? '',
                    $vol?->name ?? '',
                    $vol?->email ?? '',
                    $d->description ?? '',
                    $d->openedBy?->name ?? '',
                    $d->resolved_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $d->resolution_note ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

    /**
     * @return array{status_filter: string, event_id: int|null, search_input: string, search_term: string|null}
     */
    private function validatedDisputeListFilters(Request $request): array
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['all', Dispute::STATUS_OPEN, Dispute::STATUS_RESOLVED, Dispute::STATUS_DISMISSED])],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $statusFilter = $validated['status'] ?? 'all';
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [
            'status_filter' => $statusFilter,
            'event_id' => isset($validated['event_id']) ? (int) $validated['event_id'] : null,
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
        ];
    }

    /**
     * @param  array{status_filter: string, event_id: int|null, search_term: string|null}  $state
     * @return Builder<Dispute>
     */
    private function disputesListQuery(array $state): Builder
    {
        $query = Dispute::query()
            ->with(['attendance.event.organization', 'attendance.user', 'openedBy']);

        if ($state['event_id'] !== null) {
            $query->whereHas('attendance', fn ($q) => $q->where('event_id', $state['event_id']));
        }

        if ($state['search_term'] !== null) {
            $searchTerm = $state['search_term'];
            $query->whereHas('attendance.user', function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($state['status_filter'] !== 'all') {
            $query->where('status', $state['status_filter']);
        }

        if ($state['status_filter'] === 'all') {
            $query->orderByRaw('CASE status WHEN ? THEN 0 ELSE 1 END', [Dispute::STATUS_OPEN]);
        } else {
            $query->orderByDesc('updated_at');
        }

        return $query;
    }
}
