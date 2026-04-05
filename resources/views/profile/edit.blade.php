<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
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
