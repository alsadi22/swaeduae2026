<x-guest-layout :title="$guestPageTitle" :meta-description="__('site.meta_description')">
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        <p class="font-semibold">{{ __('Two-factor authentication') }}</p>
        <p class="mt-1 text-amber-900">{{ __('Enter the 6-digit code from your authenticator app.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.two-factor.challenge.store', $adminLocaleQ) }}" data-testid="admin-two-factor-challenge-form">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-primary-button data-testid="admin-two-factor-challenge-submit">
                {{ __('Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
