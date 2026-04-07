@php
    $volProfileLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $appShellTitle = __('Volunteer profile').' — '.__('SwaedUAE');
@endphp
<x-app-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Volunteer profile') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                <div class="max-w-2xl">
                    <p class="text-sm text-slate-600">
                        {{ __('Volunteer profile intro') }}
                    </p>
                    <div class="mt-4">
                        <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="volunteer-profile-edit-copy-page-url" />
                    </div>

                    @if (session('status') === 'erasure-request-submitted')
                        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite" data-testid="volunteer-profile-erasure-request-flash-success">
                            {{ __('Erasure request sent') }}
                        </div>
                    @endif

                    @if (! $meetsMinimum)
                        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950" role="status" aria-live="polite" data-testid="volunteer-profile-incomplete-banner">
                            {{ __('Volunteer profile incomplete banner') }}
                        </div>
                    @endif

                    <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50/80 p-4" role="status" aria-live="polite">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-900">{{ __('Profile completion') }}</p>
                            <p class="text-sm font-bold text-emerald-800"><span data-testid="profile-completion-percent">{{ $profileCompletionPercent }}</span>%</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-600">{{ __('Profile completion hint') }}</p>
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                            <div class="h-full rounded-full bg-emerald-600 transition-[width] duration-300" style="width: {{ $profileCompletionPercent }}%"></div>
                        </div>
                        @if ($profile->exists)
                            <p class="mt-3 text-xs text-slate-500" data-testid="volunteer-profile-last-saved">{{ __('Volunteer profile last saved', ['time' => $profile->updated_at->timezone(config('app.timezone'))->locale(app()->getLocale())->isoFormat('LLL')]) }}</p>
                        @endif
                    </div>

                    @if (session('status') && session('status') !== 'erasure-request-submitted')
                        <div class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite" data-testid="volunteer-profile-saved">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('volunteer.profile.update', $volProfileLocaleQ) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="volunteer_bio" :value="__('About you')" />
                            <textarea id="volunteer_bio" name="bio" rows="5" required minlength="20" maxlength="5000"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('bio', $profile->bio) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Volunteer bio minimum hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                        </div>

                        <div>
                            <x-input-label for="volunteer_skills" :value="__('Skills (optional)')" />
                            <textarea id="volunteer_skills" name="skills" rows="3" maxlength="2000"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('skills', $profile->skills) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('skills')" />
                        </div>

                        <div>
                            <x-input-label for="volunteer_availability" :value="__('Availability (optional)')" />
                            <x-text-input id="volunteer_availability" name="availability" type="text" class="mt-1 block w-full"
                                :value="old('availability', $profile->availability)" maxlength="500" />
                            <x-input-error class="mt-2" :messages="$errors->get('availability')" />
                        </div>

                        <div>
                            <x-input-label for="emergency_contact_name" :value="__('Emergency contact name')" />
                            <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full" required
                                :value="old('emergency_contact_name', $profile->emergency_contact_name)" maxlength="255" autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_name')" />
                        </div>

                        <div>
                            <x-input-label for="emergency_contact_phone" :value="__('Emergency contact phone')" />
                            <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="tel" class="mt-1 block w-full" required
                                :value="old('emergency_contact_phone', $profile->emergency_contact_phone)" maxlength="64" autocomplete="tel" />
                            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_phone')" />
                        </div>

                        <div>
                            <x-input-label for="emirates_id_masked" :value="__('Emirates ID (last digits, optional)')" />
                            <x-text-input id="emirates_id_masked" name="emirates_id_masked" type="text" class="mt-1 block w-full"
                                :value="old('emirates_id_masked', $profile->emirates_id_masked)" maxlength="32" />
                            <x-input-error class="mt-2" :messages="$errors->get('emirates_id_masked')" />
                        </div>

                        <div class="flex items-start gap-3">
                            <input type="hidden" name="notification_email_opt_in" value="0">
                            <input id="notification_email_opt_in" name="notification_email_opt_in" type="checkbox" value="1"
                                class="mt-1 rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500"
                                @checked(old('notification_email_opt_in', ($profile->notification_email_opt_in ?? true) ? '1' : '0') === '1')
                            <div>
                                <x-input-label for="notification_email_opt_in" :value="__('Email me about opportunities and reminders')" class="!inline" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save volunteer profile') }}</x-primary-button>
                        </div>
                    </form>

                    @if (auth()->user()->hasVerifiedEmail())
                        <div class="mt-10 border-t border-slate-100 pt-8">
                            @include('profile.partials.data-privacy-export', ['profileLocaleQ' => $volProfileLocaleQ, 'dataPrivacyReturn' => 'volunteer_profile'])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
