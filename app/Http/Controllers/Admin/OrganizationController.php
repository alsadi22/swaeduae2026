<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationRejectRequest;
use App\Http\Requests\Admin\OrganizationStoreRequest;
use App\Http\Requests\Admin\OrganizationUpdateRequest;
use App\Mail\OrganizationVerificationMail;
use App\Models\Organization;
use App\Models\User;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Organization::class);

        $state = $this->validatedOrganizationListFilters($request);

        $organizations = $this->organizationsQuery($state)
            ->paginate(20)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $pendingCount = Organization::query()->pendingVerification()->count();

        return view('admin.organizations.index', [
            'organizations' => $organizations,
            'filter' => $state['filter'],
            'pendingCount' => $pendingCount,
            'search' => $state['search_input'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Organization::class);

        $state = $this->validatedOrganizationListFilters($request);

        $rows = $this->organizationsQuery($state)->get();

        $filtered = $state['search_term'] !== null || $state['filter'] !== 'all';
        $filename = 'organizations-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('Name (English)'),
                __('Name (Arabic)'),
                __('Verification status'),
                __('Events'),
            ]);
            foreach ($rows as $org) {
                fputcsv($out, [
                    (string) $org->id,
                    $org->name_en,
                    $org->name_ar ?? '',
                    $org->verification_status,
                    (string) ($org->events_count ?? 0),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{filter: string, search_input: string, search_term: string|null}
     */
    private function validatedOrganizationListFilters(Request $request): array
    {
        $filter = $request->query('verification', 'all');
        if (! in_array($filter, ['all', 'pending', 'rejected', 'approved'], true)) {
            $filter = 'all';
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        return [
            'filter' => $filter,
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
        ];
    }

    /**
     * @param  array{filter: string, search_term: string|null}  $state
     * @return Builder<Organization>
     */
    private function organizationsQuery(array $state): Builder
    {
        $query = Organization::query()
            ->withCount('events')
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->orderBy('name_en');

        if ($state['filter'] === 'pending') {
            $query->pendingVerification();
        } elseif ($state['filter'] === 'rejected') {
            $query->where('verification_status', Organization::VERIFICATION_REJECTED);
        } elseif ($state['filter'] === 'approved') {
            $query->where('verification_status', Organization::VERIFICATION_APPROVED);
        }

        if ($state['search_term'] !== null) {
            $searchTerm = $state['search_term'];
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(name_en::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(name_ar::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        return $query;
    }

    public function create(): View
    {
        $this->authorize('create', Organization::class);

        return view('admin.organizations.create', ['organization' => new Organization]);
    }

    public function store(OrganizationStoreRequest $request): RedirectResponse
    {
        Organization::query()->create($request->validated());

        return redirect()
            ->route('admin.organizations.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Organization created.'));
    }

    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $organizationDocuments = $organization->documents()
            ->with('uploadedByUser')
            ->latest()
            ->get();

        return view('admin.organizations.edit', compact('organization', 'organizationDocuments'));
    }

    public function update(OrganizationUpdateRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        return redirect()
            ->route('admin.organizations.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Organization updated.'));
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        if ($organization->events()->exists()) {
            return redirect()
                ->route('admin.organizations.index', PublicLocale::queryFromRequestOrUser($request->user()))
                ->with('error', __('Cannot delete an organization that still has events.'));
        }

        $organization->delete();

        return redirect()
            ->route('admin.organizations.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Organization deleted.'));
    }

    public function approve(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('approve', $organization);

        $organization->update([
            'verification_status' => Organization::VERIFICATION_APPROVED,
            'verification_review_note' => null,
            'verification_reviewed_at' => now(),
        ]);

        $this->queueVerificationMailToRegistrant($organization->fresh(), approved: true);

        return redirect()
            ->route('admin.organizations.index', array_merge(PublicLocale::queryFromRequestOrUser($request->user()), ['verification' => 'pending']))
            ->with('status', __('Organization approved.'));
    }

    public function reject(OrganizationRejectRequest $request, Organization $organization): RedirectResponse
    {
        $this->authorize('reject', $organization);

        $organization->update([
            'verification_status' => Organization::VERIFICATION_REJECTED,
            'verification_review_note' => $request->validated()['review_note'] ?? null,
            'verification_reviewed_at' => now(),
        ]);

        $this->queueVerificationMailToRegistrant($organization->fresh(), approved: false);

        return redirect()
            ->route('admin.organizations.index', array_merge(PublicLocale::queryFromRequestOrUser($request->user()), ['verification' => 'pending']))
            ->with('status', __('Organization registration rejected.'));
    }

    private function queueVerificationMailToRegistrant(Organization $organization, bool $approved): void
    {
        if (! $organization->registered_by_user_id) {
            return;
        }

        $recipient = User::query()->find($organization->registered_by_user_id);
        if ($recipient === null || $recipient->email === '') {
            return;
        }

        Mail::to($recipient->email)->queue(
            new OrganizationVerificationMail($organization, $approved, $recipient)
        );
    }
}
