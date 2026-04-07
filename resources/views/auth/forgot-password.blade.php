@php
    $authLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $guestPageTitle = __('Forgot password').' — '.__('SwaedUAE');
@endphp
<x-guest-layout :title="$guestPageTitle" :meta-description="__('site.meta_description')">
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <div class="mb-4 flex justify-center">
        <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="forgot-password-copy-page-url" />
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email', $authLocaleQ) }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login', $authLocaleQ) }}" class="font-semibold text-emerald-800 hover:underline">{{ __('Log in') }}</a>
    </p>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('volunteer.opportunities.index', $authLocaleQ) }}" class="font-semibold text-emerald-800 hover:underline" data-testid="forgot-password-footer-opportunities">{{ __('Browse opportunities') }} →</a>
    </p>
</x-guest-layout>
