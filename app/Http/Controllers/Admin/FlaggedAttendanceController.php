<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FlaggedAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $state = $this->validatedFlaggedListFilters($request);

        $rows = $this->flaggedAttendanceListQuery($state)
            ->paginate(25)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

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
            'eventId' => $state['event_id'],
            'search' => $state['search_input'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $state = $this->validatedFlaggedListFilters($request);

        $rows = $this->flaggedAttendanceListQuery($state)->get();

        $filtered = $state['search_term'] !== null || $state['event_id'] !== null;
        $filename = 'flagged-attendance-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('Updated'),
                __('Event'),
                __('Volunteer name'),
                __('Email'),
                __('Status'),
                __('Verified minutes'),
                __('Suspicion flags on record'),
            ]);
            foreach ($rows as $row) {
                $flags = is_array($row->suspicion_flags) ? implode('; ', $row->suspicion_flags) : '';
                fputcsv($out, [
                    (string) $row->id,
                    $row->updated_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $row->event?->title_en ?? '',
                    $row->user?->name ?? '',
                    $row->user?->email ?? '',
                    $row->state,
                    $row->verifiedMinutes() !== null ? (string) $row->verifiedMinutes() : '',
                    $flags,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{event_id: int|null, search_input: string, search_term: string|null}
     */
    private function validatedFlaggedListFilters(Request $request): array
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [
            'event_id' => isset($validated['event_id']) ? (int) $validated['event_id'] : null,
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
        ];
    }

    /**
     * @param  array{event_id: int|null, search_term: string|null}  $state
     * @return Builder<Attendance>
     */
    private function flaggedAttendanceListQuery(array $state): Builder
    {
        $query = Attendance::query()
            ->withNonEmptySuspicionFlags()
            ->with(['event.organization', 'user'])
            ->orderByDesc('updated_at');

        if ($state['event_id'] !== null) {
            $query->where('event_id', $state['event_id']);
        }

        if ($state['search_term'] !== null) {
            $searchTerm = $state['search_term'];
            $query->whereHas('user', function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        return $query;
    }
}
