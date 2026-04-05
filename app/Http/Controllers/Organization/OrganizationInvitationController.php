<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationInvitationRequest;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Notifications\OrganizationStaffInvitation;
use App\Support\AuthRedirect;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrganizationInvitationController extends Controller
{
    public function store(StoreOrganizationInvitationRequest $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        if ($organization === null || ! $organization->isApproved()) {
            abort(403);
        }

        $email = strtolower($request->validated('email'));
        $role = $request->validated('role');

        $plainToken = Str::random(64);

        DB::transaction(function () use ($organization, $email, $role, $plainToken, $request): void {
            OrganizationInvitation::query()
                ->where('organization_id', $organization->id)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->delete();

            OrganizationInvitation::query()->create([
                'organization_id' => $organization->id,
                'email' => $email,
                'role' => $role,
                'token_hash' => OrganizationInvitation::hashToken($plainToken),
                'invited_by_user_id' => $request->user()->id,
                'expires_at' => now()->addDays(14),
            ]);
        });

        $invitation = OrganizationInvitation::query()
            ->where('organization_id', $organization->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->latest('id')
            ->firstOrFail();

        $this->sendStaffInvitationEmail($invitation, $plainToken, $request->user());

        return redirect()
            ->to(AuthRedirect::homeForUser($request->user()))
            ->with('status', __('Organization invitation sent.'));
    }

    public function resend(Request $request, OrganizationInvitation $invitation): RedirectResponse
    {
        $this->authorize('resend', $invitation);

        $plainToken = Str::random(64);
        $invitation->update([
            'token_hash' => OrganizationInvitation::hashToken($plainToken),
            'invited_by_user_id' => $request->user()->id,
            'expires_at' => now()->addDays(14),
        ]);
        $invitation->refresh();

        $this->sendStaffInvitationEmail($invitation, $plainToken, $request->user());

        return redirect()
            ->to(AuthRedirect::homeForUser($request->user()))
            ->with('status', __('Organization invitation resent.'));
    }

    public function destroy(Request $request, OrganizationInvitation $invitation): RedirectResponse
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return redirect()
            ->to(AuthRedirect::homeForUser($request->user()))
            ->with('status', __('Organization invitation cancelled.'));
    }

    private function sendStaffInvitationEmail(OrganizationInvitation $invitation, string $plainToken, User $inviter): void
    {
        $invitation->load('organization');
        $acceptUrl = url('/organization/join/'.$plainToken.'?'.http_build_query(PublicLocale::queryForUser($inviter)));

        Notification::route('mail', $invitation->email)->notify(
            new OrganizationStaffInvitation($invitation, $acceptUrl)
        );
    }
}
