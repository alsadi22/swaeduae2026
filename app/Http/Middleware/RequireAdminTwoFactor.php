<?php

namespace App\Http\Middleware;

use App\Support\PublicLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RequireAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('swaeduae.security.admin_two_factor_required', false)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user === null || ! $user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        if ($request->routeIs(['admin.two-factor.setup', 'admin.two-factor.setup.store'])) {
            return $next($request);
        }

        if ($user->two_factor_confirmed_at === null || $user->two_factor_secret === null) {
            return redirect()->route('admin.two-factor.setup', PublicLocale::queryFromRequestOrUser($user));
        }

        if (! $request->session()->has('admin_two_factor_verified_at')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login', PublicLocale::query())
                ->withErrors(['email' => __('Please sign in again and complete two-factor authentication.')]);
        }

        return $next($request);
    }
}
