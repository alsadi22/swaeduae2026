@php
    $pageTitle = __('Set up two-factor authentication').' — '.__('SwaedUAE');
@endphp
<x-guest-layout :title="$pageTitle" :meta-description="__('site.meta_description')">
    <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900">
        <p class="font-semibold">{{ __('Set up two-factor authentication') }}</p>
        <p class="mt-1 text-slate-700">{{ __('Scan this QR code with your authenticator app, then enter the code to confirm.') }}</p>
    </div>

    <div class="mb-6 flex justify-center rounded-xl border border-slate-200 bg-white p-4" aria-hidden="true" data-testid="admin-two-factor-qr">
        {!! $qrSvg !!}
    </div>

    <form method="POST" action="{{ route('admin.two-factor.setup.store', $adminLocaleQ) }}" data-testid="admin-two-factor-setup-form">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-primary-button data-testid="admin-two-factor-setup-submit">
                {{ __('Confirm and enable') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
