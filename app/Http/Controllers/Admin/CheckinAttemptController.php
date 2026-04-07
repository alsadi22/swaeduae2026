<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckinAttemptController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $state = $this->validatedCheckinListFilters($request);

        $attempts = $this->checkinAttemptsListQuery($state)
            ->paginate(40)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $filterEvents = Event::query()
            ->whereIn('id', CheckinAttempt::query()->select('event_id')->distinct())
            ->orderBy('title_en')
            ->get();

        return view('admin.checkin-attempts.index', [
            'attempts' => $attempts,
            'filterEvents' => $filterEvents,
            'outcomeFilter' => $state['outcome_filter'],
            'eventId' => $state['event_id'],
            'search' => $state['search_input'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', CheckinAttempt::class);

        $state = $this->validatedCheckinListFilters($request);

        $rows = $this->checkinAttemptsListQuery($state)->get();

        $filtered = $state['outcome_filter'] !== 'all'
            || $state['event_id'] !== null
            || $state['search_term'] !== null;
        $filename = 'checkin-attempts-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('When'),
                __('Event'),
                __('Volunteer name'),
                __('Email'),
                __('Type'),
                __('Outcome'),
                __('Distance meters'),
                __('Rejection reason'),
                __('Suspicion flags'),
            ]);
            foreach ($rows as $a) {
                $flags = is_array($a->flags) ? implode('; ', $a->flags) : '';
                fputcsv($out, [
                    (string) $a->id,
                    $a->created_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $a->event?->title_en ?? '',
                    $a->user?->name ?? '',
                    $a->user?->email ?? '',
                    $a->attempt_type,
                    $a->outcome,
                    $a->distance_meters !== null ? (string) $a->distance_meters : '',
                    $a->rejection_reason ?? '',
                    $flags,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{outcome_filter: string, event_id: int|null, search_input: string, search_term: string|null}
     */
    private function validatedCheckinListFilters(Request $request): array
    {
        $validated = $request->validate([
            'outcome' => ['nullable', 'string', Rule::in(['all', 'accepted', 'suspicious', 'rejected'])],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $outcomeFilter = $validated['outcome'] ?? 'all';
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [
            'outcome_filter' => $outcomeFilter,
            'event_id' => isset($validated['event_id']) ? (int) $validated['event_id'] : null,
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
        ];
    }

    /**
     * @param  array{outcome_filter: string, event_id: int|null, search_term: string|null}  $state
     * @return Builder<CheckinAttempt>
     */
    private function checkinAttemptsListQuery(array $state): Builder
    {
        $query = CheckinAttempt::query()
            ->with(['event.organization', 'user'])
            ->orderByDesc('created_at');

        if ($state['outcome_filter'] !== 'all') {
            $query->where('outcome', $state['outcome_filter']);
        }

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
