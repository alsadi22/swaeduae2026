@php
    $authLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $verifyEmailTitle = __('Verify your email').' — '.__('SwaedUAE');
@endphp
<x-guest-layout :title="$verifyEmailTitle" :meta-description="__('site.meta_description')">
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    <div class="mb-4 flex justify-center">
        <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="verify-email-copy-page-url" />
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600" role="status" aria-live="polite" data-testid="verify-email-link-sent">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send', $authLocaleQ) }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout', $authLocaleQ) }}">
            @csrf

            <button type="submit" class="rounded-md text-sm text-slate-600 underline hover:text-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
