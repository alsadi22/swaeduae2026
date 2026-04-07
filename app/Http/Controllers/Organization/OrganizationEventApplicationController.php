<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\RejectOrganizationEventApplicationRequest;
use App\Mail\EventApplicationReviewedMail;
use App\Models\Event;
use App\Models\EventApplication;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationEventApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $state = $this->validatedApplicationsFilters($request);

        $applications = $this->applicationsQuery($state)
            ->with(['event', 'user'])
            ->paginate(25)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $filterEvents = Event::query()
            ->where('organization_id', $state['organization_id'])
            ->whereIn('id', EventApplication::query()->select('event_id')->distinct())
            ->orderBy('title_en')
            ->get();

        $canReviewApplications = $request->user()->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator'])
            && $request->user()->organization?->isApproved();

        return view('organization.event-applications.index', [
            'applications' => $applications,
            'filterEvents' => $filterEvents,
            'statusFilter' => $state['status_filter'],
            'eventId' => $state['event_id'],
            'search' => $state['search_input'],
            'sort' => $state['sort'],
            'canReviewApplications' => $canReviewApplications,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $state = $this->validatedApplicationsFilters($request);

        $rows = $this->applicationsQuery($state)
            ->with(['event', 'user'])
            ->get();

        $org = $request->user()->organization;
        $slug = Str::slug((string) ($org?->name_en ?? 'organization')) ?: 'organization';
        $filtered = $state['search_term'] !== null
            || $state['status_filter'] !== 'all'
            || $state['event_id'] !== null;
        $filename = 'event-applications-'.$slug.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                __('Status'),
                __('Submitted at'),
                __('Event'),
                __('Volunteer name'),
                __('Email'),
                __('Application message'),
                __('Review note'),
            ]);
            foreach ($rows as $app) {
                $event = $app->event;
                $user = $app->user;
                fputcsv($out, [
                    $app->status,
                    $app->created_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $event ? $event->title_en : '',
                    $user?->name ?? '',
                    $user?->email ?? '',
                    $app->message ?? '',
                    $app->review_note ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{
     *     organization_id: int,
     *     status_filter: string,
     *     event_id: int|null,
     *     search_input: string,
     *     search_term: string|null,
     *     sort: string
     * }
     */
    private function validatedApplicationsFilters(Request $request): array
    {
        Gate::authorize('view-organization-event-applications');

        $organizationId = $request->user()->organization_id;
        if ($organizationId === null) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'all',
                    EventApplication::STATUS_PENDING,
                    EventApplication::STATUS_APPROVED,
                    EventApplication::STATUS_REJECTED,
                    EventApplication::STATUS_WITHDRAWN,
                ]),
            ],
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where('organization_id', $organizationId),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', Rule::in(['default', 'submitted_asc', 'submitted_desc'])],
        ]);

        $statusFilter = $validated['status'] ?? 'all';
        $eventId = $validated['event_id'] ?? null;
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;
        $sort = $validated['sort'] ?? 'default';
        if ($sort === '') {
            $sort = 'default';
        }

        return [
            'organization_id' => $organizationId,
            'status_filter' => $statusFilter,
            'event_id' => $eventId,
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
            'sort' => $sort,
        ];
    }

    /**
     * @param  array{
     *     organization_id: int,
     *     status_filter: string,
     *     event_id: int|null,
     *     search_term: string|null,
     *     sort: string
     * }  $state
     * @return Builder<EventApplication>
     */
    private function applicationsQuery(array $state): Builder
    {
        $query = EventApplication::query()
            ->whereHas('event', fn ($q) => $q->where('organization_id', $state['organization_id']));

        if ($state['status_filter'] !== 'all') {
            $query->where('status', $state['status_filter']);
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

        if ($state['sort'] === 'submitted_asc') {
            $query->orderBy('created_at');
        } elseif ($state['sort'] === 'submitted_desc') {
            $query->orderByDesc('created_at');
        } elseif ($state['status_filter'] === 'all' && $state['event_id'] === null) {
            $query->orderByRaw('CASE status WHEN ? THEN 0 ELSE 1 END', [EventApplication::STATUS_PENDING])
                ->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query;
    }

    public function approve(Request $request, EventApplication $event_application): RedirectResponse
    {
        $this->authorize('organizationPortalReview', $event_application);

        if (! $event_application->isPending()) {
            return back()->with('error', __('Only pending applications can be updated.'));
        }

        $event_application->update([
            'status' => EventApplication::STATUS_APPROVED,
            'review_note' => null,
        ]);

        $event_application->refresh();
        $event_application->load(['event', 'user']);
        if ($event_application->user) {
            Mail::to($event_application->user)->send(
                new EventApplicationReviewedMail($event_application, EventApplicationReviewedMail::OUTCOME_APPROVED)
            );
        }

        return back()->with('status', __('Application approved.'));
    }

    public function reject(RejectOrganizationEventApplicationRequest $request, EventApplication $event_application): RedirectResponse
    {
        if (! $event_application->isPending()) {
            return back()->with('error', __('Only pending applications can be updated.'));
        }

        $note = $request->validated('review_note');

        $event_application->update([
            'status' => EventApplication::STATUS_REJECTED,
            'review_note' => filled($note) ? $note : null,
        ]);

        $event_application->refresh();
        $event_application->load(['event', 'user']);
        if ($event_application->user) {
            Mail::to($event_application->user)->send(
                new EventApplicationReviewedMail($event_application, EventApplicationReviewedMail::OUTCOME_REJECTED)
            );
        }

        return back()->with('status', __('Application rejected.'));
    }
}
