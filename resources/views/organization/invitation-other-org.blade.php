@php
    $orgLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $appShellTitle = __('Cannot accept invitation').' — '.__('SwaedUAE');
@endphp
<x-app-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Cannot accept invitation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden border border-red-200 bg-red-50/90 p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-red-950">{{ __('Organization invitation other org body') }}</p>
                <p class="mt-4 text-sm text-red-900/90">{{ __('Organization invitation other org hint') }}</p>
                <div class="mt-4 flex flex-wrap justify-end">
                    <x-copy-filtered-list-url-button class="[&_button]:border-red-200 [&_button]:bg-white [&_button]:text-red-900" test-id="organization-invitation-other-org-copy-page-url" />
                </div>
                <a href="{{ route('organization.dashboard', $orgLocaleQ) }}" class="mt-6 inline-flex text-sm font-bold text-red-900 underline hover:text-red-950">{{ __('Organization dashboard') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
