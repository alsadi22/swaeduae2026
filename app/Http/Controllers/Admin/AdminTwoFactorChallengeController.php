<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminTwoFactorManager;
use App\Support\AuthRedirect;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! config('swaeduae.security.admin_two_factor_required', false)) {
            return redirect()->route('admin.login', PublicLocale::query());
        }

        if (! $request->session()->has('admin_two_factor_login.id')) {
            return redirect()->route('admin.login', PublicLocale::query());
        }

        $guestPageTitle = __('Two-factor authentication').' — '.__('SwaedUAE');

        return view('admin.auth.two-factor-challenge', [
            'guestPageTitle' => $guestPageTitle,
            'adminLocaleQ' => PublicLocale::query(),
        ]);
    }

    public function store(Request $request, AdminTwoFactorManager $manager): RedirectResponse
    {
        if (! config('swaeduae.security.admin_two_factor_required', false)) {
            return redirect()->route('admin.login', PublicLocale::query());
        }

        $id = $request->session()->get('admin_two_factor_login.id');
        if (! is_int($id) && ! is_string($id)) {
            return redirect()->route('admin.login', PublicLocale::query());
        }

        $user = User::query()->find($id);
        if ($user === null || ! $user->hasAnyRole(['admin', 'super-admin'])) {
            $request->session()->forget(['admin_two_factor_login.id', 'admin_two_factor_login.remember']);

            return redirect()->route('admin.login', PublicLocale::query());
        }

        if ($user->two_factor_secret === null || $user->two_factor_confirmed_at === null) {
            $request->session()->forget(['admin_two_factor_login.id', 'admin_two_factor_login.remember']);

            return redirect()->route('admin.login', PublicLocale::query());
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        if (! $manager->verify($user->two_factor_secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('Invalid authentication code.'),
            ]);
        }

        $remember = (bool) $request->session()->get('admin_two_factor_login.remember', false);
        $request->session()->forget(['admin_two_factor_login.id', 'admin_two_factor_login.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('admin_two_factor_verified_at', now()->getTimestamp());

        return redirect()->intended(AuthRedirect::homeForUser($user));
    }
}
