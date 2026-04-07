@php
    $invWrongLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $appShellTitle = __('Wrong account').' — '.__('SwaedUAE');
@endphp
<x-app-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Wrong account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden border border-amber-200 bg-amber-50/90 p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-amber-950">{{ __('Organization invitation wrong account', ['email' => $invitation->email]) }}</p>
                <p class="mt-4 text-sm text-amber-900/90">{{ __('Organization invitation wrong account hint') }}</p>
                <div class="mt-4 flex flex-wrap justify-end">
                    <x-copy-filtered-list-url-button class="[&_button]:border-amber-200 [&_button]:bg-white [&_button]:text-amber-950" test-id="organization-invitation-wrong-account-copy-page-url" />
                </div>
                <form method="POST" action="{{ route('logout', $invWrongLocaleQ) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-emerald-900 underline hover:text-emerald-950">{{ __('Log Out') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
