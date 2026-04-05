<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\OrganizationInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Organization portal entry (verification-aware; deeper portal features on roadmap).
 */
class OrganizationDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;

        $canInviteStaff = $request->user()->hasRole('org-owner')
            && $organization !== null
            && $organization->isApproved();

        $invitationSearch = '';

        /** @var Collection<int, OrganizationInvitation> $pendingInvitations */
        $pendingInvitations = collect();
        if ($canInviteStaff) {
            $validated = $request->validate([
                'invitation_search' => ['nullable', 'string', 'max:100'],
            ]);
            $invitationSearch = trim((string) ($validated['invitation_search'] ?? ''));

            $invitationQuery = OrganizationInvitation::query()
                ->where('organization_id', $organization->id)
                ->whereNull('accepted_at')
                ->orderByDesc('created_at');

            if ($invitationSearch !== '') {
                // Substring match avoids SQL LIKE wildcard quirks with `_` / `%` in patterns.
                $invitationQuery->whereRaw('strpos(lower(email::text), lower(?::text)) > 0', [$invitationSearch]);
            }

            $pendingInvitations = $invitationQuery->get();
        }

        $canViewOrganizationApplications = Gate::forUser($request->user())->allows('view-organization-event-applications');
        $canViewOrganizationEvents = Gate::forUser($request->user())->allows('view-organization-events');
        $pendingOrganizationApplicationsCount = 0;
        if ($canViewOrganizationApplications && $organization !== null) {
            $pendingOrganizationApplicationsCount = EventApplication::query()
                ->where('status', EventApplication::STATUS_PENDING)
                ->whereHas('event', fn ($q) => $q->where('organization_id', $organization->id))
                ->count();
        }

        $upcomingOrganizationEventsCount = 0;
        if ($canViewOrganizationEvents && $organization !== null) {
            $upcomingOrganizationEventsCount = Event::query()
                ->where('organization_id', $organization->id)
                ->where('event_ends_at', '>=', now())
                ->count();
        }

        return view('organization.dashboard', compact(
            'organization',
            'canInviteStaff',
            'pendingInvitations',
            'invitationSearch',
            'canViewOrganizationApplications',
            'canViewOrganizationEvents',
            'pendingOrganizationApplicationsCount',
            'upcomingOrganizationEventsCount',
        ));
    }
}
