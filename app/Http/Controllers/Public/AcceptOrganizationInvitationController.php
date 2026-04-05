<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Support\AuthRedirect;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcceptOrganizationInvitationController extends Controller
{
    public function show(Request $request, string $token): View|RedirectResponse
    {
        if (! preg_match('/^[A-Za-z0-9]{64}$/', $token)) {
            abort(404);
        }

        $invitation = OrganizationInvitation::query()
            ->where('token_hash', OrganizationInvitation::hashToken($token))
            ->with('organization')
            ->first();

        if ($invitation === null || ! $invitation->isValid()) {
            return view('organization.invitation-invalid');
        }

        if (! $request->user()) {
            return redirect()->guest(route('login', PublicLocale::query(), false));
        }

        $user = $request->user();

        if (strtolower((string) $user->email) !== strtolower($invitation->email)) {
            return view('organization.invitation-wrong-account', compact('invitation'));
        }

        if ($user->organization_id !== null
            && (int) $user->organization_id !== (int) $invitation->organization_id) {
            return view('organization.invitation-other-org', compact('invitation'));
        }

        if ((int) $user->organization_id === (int) $invitation->organization_id) {
            return redirect()
                ->to(AuthRedirect::homeForUser($user))
                ->with('status', __('Already an organization member.'));
        }

        DB::transaction(function () use ($user, $invitation): void {
            $user->forceFill(['organization_id' => $invitation->organization_id])->save();
            $user->assignRole($invitation->role);
            $invitation->update(['accepted_at' => now()]);
        });

        return $this->redirectAfterInvitation(__('You joined the organization.'), $user->fresh());
    }

    private function redirectAfterInvitation(string $message, ?User $user): RedirectResponse
    {
        if ($user !== null && ! $user->hasVerifiedEmail()) {
            $noticeQuery = $this->verificationNoticeQuery($user);

            return redirect()
                ->to(route('verification.notice', $noticeQuery, false))
                ->with('status', $message);
        }

        return redirect()
            ->to($user !== null ? AuthRedirect::homeForUser($user) : route('home', PublicLocale::query(), false))
            ->with('status', $message);
    }

    /**
     * @return array{lang: string}
     */
    private function verificationNoticeQuery(User $user): array
    {
        if (is_string($user->locale_preferred) && in_array($user->locale_preferred, ['en', 'ar'], true)) {
            return ['lang' => $user->locale_preferred];
        }

        return PublicLocale::query();
    }
}
