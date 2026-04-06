<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectEventApplicationRequest;
use App\Mail\EventApplicationReviewedMail;
use App\Models\Event;
use App\Models\EventApplication;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EventApplication::class);

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
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
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

        $query = EventApplication::query()
            ->with(['event.organization', 'user']);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
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

        if ($sort === 'submitted_asc') {
            $query->orderBy('created_at');
        } elseif ($sort === 'submitted_desc') {
            $query->orderByDesc('created_at');
        } elseif ($statusFilter === 'all' && $eventId === null) {
            $query->orderByRaw('CASE status WHEN ? THEN 0 ELSE 1 END', [EventApplication::STATUS_PENDING])
                ->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $applications = $query->paginate(25)->withQueryString()->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $filterEvents = Event::query()
            ->whereIn('id', EventApplication::query()->select('event_id')->distinct())
            ->orderBy('title_en')
            ->get();

        return view('admin.event-applications.index', [
            'applications' => $applications,
            'filterEvents' => $filterEvents,
            'statusFilter' => $statusFilter,
            'eventId' => $eventId,
            'search' => $searchInput,
            'sort' => $sort,
        ]);
    }

    public function approve(EventApplication $event_application): RedirectResponse
    {
        $this->authorize('review', $event_application);

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

    public function reject(RejectEventApplicationRequest $request, EventApplication $event_application): RedirectResponse
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
