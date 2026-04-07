<?php

namespace App\Http\Controllers;

use App\Mail\DataErasureRequestedMail;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileErasureRequestController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $privacyInbox = config('swaeduae.mail.privacy');
        if (! is_string($privacyInbox) || $privacyInbox === '') {
            throw ValidationException::withMessages([
                'erasure' => __('Erasure request unavailable'),
            ]);
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
            'return' => ['nullable', 'string', Rule::in(['volunteer_profile'])],
        ]);

        $user = $request->user();
        if ($user === null || ! $user->hasVerifiedEmail()) {
            abort(403);
        }

        Mail::to($privacyInbox)->queue(new DataErasureRequestedMail(
            $user,
            isset($validated['message']) ? trim((string) $validated['message']) : null,
        ));

        $localeQ = PublicLocale::queryFromRequestOrUser($user);

        if (($validated['return'] ?? null) === 'volunteer_profile' && $user->hasRole('volunteer')) {
            return redirect()
                ->route('volunteer.profile.edit', $localeQ)
                ->with('status', 'erasure-request-submitted');
        }

        return redirect()
            ->route('profile.edit', $localeQ)
            ->with('status', 'erasure-request-submitted');
    }
}
