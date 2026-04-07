<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthRedirect;
use App\Support\IntendedUrl;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * @var list<string>
     */
    private const STAFF_ROLES = ['admin', 'super-admin', 'org-owner', 'org-manager', 'org-coordinator', 'org-viewer'];

    public function redirect(Request $request): RedirectResponse|SymfonyRedirect
    {
        if (! $this->googleConfigured()) {
            return redirect()
                ->route('login', PublicLocale::queryFromRequestOrUser(null))
                ->withErrors(['email' => __('Google sign-in is not available.')]);
        }

        IntendedUrl::captureFromQuery($request);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()
                ->route('login', PublicLocale::queryFromRequestOrUser(null))
                ->withErrors(['email' => __('Google sign-in is not available.')]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()
                ->route('login', PublicLocale::queryFromRequestOrUser(null))
                ->withErrors(['email' => __('Google sign-in session expired. Please try again.')]);
        } catch (Throwable) {
            return redirect()
                ->route('login', PublicLocale::queryFromRequestOrUser(null))
                ->withErrors(['email' => __('Google sign-in failed. Please try again or use email and password.')]);
        }

        $email = $googleUser->getEmail();
        if (! is_string($email) || $email === '') {
            return redirect()
                ->route('login', PublicLocale::queryFromRequestOrUser(null))
                ->withErrors(['email' => __('Google did not return an email address.')]);
        }

        $email = Str::lower($email);
        $googleId = (string) $googleUser->getId();

        $user = User::query()->where('google_id', $googleId)->first()
            ?? User::query()->where('email', $email)->first();

        if ($user !== null) {
            if ($user->hasAnyRole(self::STAFF_ROLES)) {
                return redirect()
                    ->route('login', PublicLocale::queryFromRequestOrUser(null))
                    ->withErrors(['email' => __('This account must sign in with email and password.')]);
            }

            $updates = [];
            if ($user->google_id === null) {
                $updates['google_id'] = $googleId;
            }
            if ($user->email_verified_at === null) {
                $updates['email_verified_at'] = now();
            }
            if ($updates !== []) {
                $user->update($updates);
            }
        } else {
            $user = $this->createVolunteerFromGoogle($googleUser, $googleId, $email);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(AuthRedirect::homeForUser($user));
    }

    private function createVolunteerFromGoogle(object $googleUser, string $googleId, string $email): User
    {
        $name = $googleUser->getName();
        if (! is_string($name) || trim($name) === '') {
            $name = Str::before($email, '@');
        }
        $name = trim($name);
        $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY) ?: [$name];
        $first = $parts[0];
        $last = $parts[1] ?? '';

        $locale = app()->getLocale();
        if (! in_array($locale, ['en', 'ar'], true)) {
            $locale = config('app.locale', 'en');
        }
        if (! in_array($locale, ['en', 'ar'], true)) {
            $locale = 'en';
        }

        Role::firstOrCreate(
            ['name' => 'volunteer', 'guard_name' => 'web']
        );

        $user = User::create([
            'name' => $name,
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'google_id' => $googleId,
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'locale_preferred' => $locale,
            'terms_accepted_at' => now(),
        ]);
        $user->assignRole('volunteer');

        return $user;
    }

    private function googleConfigured(): bool
    {
        $id = config('services.google.client_id');
        $secret = config('services.google.client_secret');

        return is_string($id) && $id !== '' && is_string($secret) && $secret !== '';
    }
}
