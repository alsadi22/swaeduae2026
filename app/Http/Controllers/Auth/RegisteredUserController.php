<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\IntendedUrl;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        IntendedUrl::captureFromQuery($request);

        return view('auth.register-volunteer');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'locale_preferred' => ['required', 'string', 'in:en,ar'],
            'terms' => ['accepted'],
        ]);

        $fullName = trim($validated['first_name'].' '.$validated['last_name']);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'locale_preferred' => $validated['locale_preferred'],
            'terms_accepted_at' => now(),
            'password' => Hash::make($validated['password']),
        ]);

        Role::firstOrCreate(
            ['name' => 'volunteer', 'guard_name' => 'web']
        );
        $user->assignRole('volunteer');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice', ['lang' => $validated['locale_preferred']]);
    }
}
