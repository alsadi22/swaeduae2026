<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminTwoFactorManager;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTwoFactorSetupController extends Controller
{
    public function show(Request $request, AdminTwoFactorManager $manager): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['admin', 'super-admin']), 403);

        if ($user->two_factor_secret === null) {
            $user->forceFill([
                'two_factor_secret' => $manager->generateSecretKey(),
            ])->save();
            $user->refresh();
        }

        $svg = $manager->qrCodeSvg((string) $user->email, (string) $user->two_factor_secret);

        return view('admin.auth.two-factor-setup', [
            'qrSvg' => $svg,
            'adminLocaleQ' => PublicLocale::queryFromRequestOrUser($user),
        ]);
    }

    public function store(Request $request, AdminTwoFactorManager $manager): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['admin', 'super-admin']), 403);

        if ($user->two_factor_secret === null) {
            return redirect()->route('admin.two-factor.setup', PublicLocale::queryFromRequestOrUser($user));
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        if (! $manager->verify($user->two_factor_secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('Invalid authentication code.'),
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $request->session()->put('admin_two_factor_verified_at', now()->getTimestamp());

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::queryFromRequestOrUser($user))
            ->with('status', __('Two-factor authentication is now enabled.'));
    }
}
