@php
    $profileLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $appShellTitle = __('Profile').' — '.__('SwaedUAE');
@endphp
<x-app-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status') === 'erasure-request-submitted')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 sm:px-6 lg:px-8" role="status" aria-live="polite" data-testid="profile-erasure-request-flash-success">
                    {{ __('Erasure request sent') }}
                </div>
            @endif
            <div class="flex flex-wrap justify-end">
                <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="profile-edit-copy-page-url" />
            </div>
            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                @include('profile.partials.data-privacy-export')
            </div>

            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
