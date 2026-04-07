@php
    $authLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $guestPageTitle = (! empty($authPortal) && $authPortal === 'admin')
        ? __('Admin sign-in').' — '.__('SwaedUAE')
        : __('Log in').' — '.__('SwaedUAE');
@endphp
<x-guest-layout :title="$guestPageTitle" :meta-description="__('site.meta_description')">
    @if (! empty($authPortal) && $authPortal === 'admin')
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="font-semibold">{{ __('Admin sign-in') }}</p>
            <p class="mt-1 text-amber-900">{{ __('You are signing in to the staff admin area.') }}</p>
        </div>
    @endif

    @if (! empty($authPortal) && $authPortal === 'admin')
        <div class="mb-4 flex justify-center">
            <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="login-copy-page-url" />
        </div>
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (empty($authPortal))
        @include('auth.partials.google-sign-in', ['authLocaleQ' => $authLocaleQ])
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs font-semibold uppercase tracking-wide">
                <span class="bg-white px-3 text-slate-500">{{ __('Or sign in with email') }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login', $authLocaleQ) }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-slate-600 underline hover:text-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" href="{{ route('password.request', $authLocaleQ) }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="sm:ms-3">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>
    </form>

    @if (empty($authPortal))
        <div class="mt-8 border-t border-slate-200 pt-6 text-center text-sm text-slate-600">
            <p class="font-medium text-slate-800">{{ __('Join as Volunteer') }}</p>
            <a href="{{ route('register.volunteer', array_merge(\App\Support\IntendedUrl::queryParamsForRelativeUri((string) request()->query('return', '')), $authLocaleQ)) }}" class="mt-2 inline-block font-semibold text-emerald-800 hover:underline">{{ __('Create volunteer account') }}</a>
            <p class="mt-4 font-medium text-slate-800">{{ __('Register Organization') }}</p>
            <a href="{{ route('register.organization', $authLocaleQ) }}" class="mt-2 inline-block font-semibold text-emerald-800 hover:underline">{{ __('Start organization registration') }}</a>
        </div>
    @endif
</x-guest-layout>
