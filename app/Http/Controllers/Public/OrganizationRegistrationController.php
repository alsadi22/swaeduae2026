<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\RegisterOrganizationRequest;
use App\Mail\OrganizationRegistrationStaffMail;
use App\Models\Organization;
use App\Models\User;
use App\Support\IntendedUrl;
use App\Support\PublicLocale;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Public organization signup: creates a pending organization and org-owner account (email verification required).
 */
class OrganizationRegistrationController extends Controller
{
    public function show(Request $request): View
    {
        IntendedUrl::captureFromQuery($request);

        return view('auth.register-organization');
    }

    public function store(RegisterOrganizationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $fullName = trim($validated['first_name'].' '.$validated['last_name']);

        $user = DB::transaction(function () use ($validated, $fullName) {
            $organization = Organization::query()->create([
                'name_en' => $validated['name_en'],
                'name_ar' => $validated['name_ar'] ?? null,
                'verification_status' => Organization::VERIFICATION_PENDING,
            ]);

            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'locale_preferred' => $validated['locale_preferred'],
                'terms_accepted_at' => now(),
                'password' => Hash::make($validated['password']),
            ]);

            $user->forceFill(['organization_id' => $organization->id])->save();

            $organization->update([
                'registered_by_user_id' => $user->id,
            ]);

            Role::firstOrCreate(['name' => 'org-owner', 'guard_name' => 'web']);
            $user->syncRoles(['org-owner']);

            return $user;
        });

        event(new Registered($user));

        $adminAlerts = config('swaeduae.mail.admin_alerts');
        if (is_string($adminAlerts) && $adminAlerts !== '') {
            $organization = Organization::query()->findOrFail($user->organization_id);
            Mail::to($adminAlerts)->queue(new OrganizationRegistrationStaffMail($organization, $user));
        }

        Auth::login($user);

        return redirect()->to(route('verification.notice', PublicLocale::query(), false));
    }
}
