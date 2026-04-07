<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\AuthRedirect;
use App\Support\IntendedUrl;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        IntendedUrl::captureFromQuery($request);

        return view('auth.login', [
            'authPortal' => $request->routeIs('admin.login') ? 'admin' : null,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if (
            $user !== null
            && config('swaeduae.security.admin_two_factor_required', false)
            && $user->hasAnyRole(['admin', 'super-admin'])
            && $user->two_factor_confirmed_at !== null
            && $user->two_factor_secret !== null
        ) {
            $remember = $request->boolean('remember');
            Auth::logout();
            $request->session()->put('admin_two_factor_login.id', $user->getAuthIdentifier());
            $request->session()->put('admin_two_factor_login.remember', $remember);

            return redirect()->route('admin.two-factor.challenge', PublicLocale::query());
        }

        $default = $user ? AuthRedirect::homeForUser($user) : route('dashboard', absolute: false);

        return redirect()->intended($default);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $homeQuery = PublicLocale::queryFromRequestOrUser($request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home', $homeQuery);
    }
}
