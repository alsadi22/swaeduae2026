@php
    $pageTitle = __('Contact and support').' — '.__('SwaedUAE');
    $localeQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Contact and support'), 'url' => route('contact.show', $localeQ, true)],
    ];
    $contactTypeValue = old('contact_type', $contactTypePrefill ?? 'general');
    $supportTopicValue = old('topic', $supportTopicPrefill ?? 'other');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Contact and support') }}</h1>
        <p class="mt-4 max-w-3xl text-slate-600 leading-relaxed">{{ __('Contact support page intro') }}</p>
        <div class="mt-6">
            <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="contact-copy-page-url" />
        </div>

        @if (session('success'))
            <div class="mt-8 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite" data-testid="contact-form-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Institutional / general contact --}}
        <section id="institutional-contact" class="mt-14 border-t border-slate-200 pt-14" aria-labelledby="contact-form-heading">
            <h2 id="contact-form-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('Contact') }}</h2>
            <p class="mt-3 max-w-3xl text-slate-600 leading-relaxed">{{ __('Contact intro') }}</p>
            <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ __('Contact routes to info') }}</p>
            <p class="mt-3 text-sm text-slate-500">
                <a href="mailto:{{ config('swaeduae.mail.info') }}" class="font-semibold text-emerald-800 hover:underline">{{ config('swaeduae.mail.info') }}</a>
            </p>

            <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-4 lg:pt-2">
                    <p class="text-sm text-slate-500">
                        <span class="font-medium text-slate-700">{{ config('swaeduae.domain') }}</span>
                    </p>
                </div>
                <div class="lg:col-span-8">
                    <div class="card-surface p-6 sm:p-8">
                        <form method="post" action="{{ route('contact.store', $localeQ) }}" class="relative space-y-6">
                            @csrf
                            <div class="pointer-events-none absolute -left-[9999px] top-0 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                                <label for="contact_trap" class="sr-only">{{ __('Leave this field empty') }}</label>
                                <input type="text" name="contact_trap" id="contact_trap" value="" tabindex="-1" autocomplete="off">
                            </div>
                            <div>
                                <label for="contact_name" class="block text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                                <input type="text" name="name" id="contact_name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact_email" class="block text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                                <input type="email" name="email" id="contact_email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact_phone" class="block text-sm font-semibold text-slate-700">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                                <input type="text" name="phone" id="contact_phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact_type" class="block text-sm font-semibold text-slate-700">{{ __('Contact inquiry type') }}</label>
                                <select name="contact_type" id="contact_type" data-testid="contact-type-select" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:max-w-md">
                                    <option value="general" @selected($contactTypeValue === 'general')>{{ __('Contact type general') }}</option>
                                    <option value="partnership" @selected($contactTypeValue === 'partnership')>{{ __('Contact type partnership') }}</option>
                                    <option value="media" @selected($contactTypeValue === 'media')>{{ __('Contact type media') }}</option>
                                    <option value="youth_programmes" @selected($contactTypeValue === 'youth_programmes')>{{ __('Contact type youth programmes') }}</option>
                                    <option value="data_rights" @selected($contactTypeValue === 'data_rights')>{{ __('Contact type data rights') }}</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Contact inquiry type hint') }}</p>
                                @error('contact_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact_subject" class="block text-sm font-semibold text-slate-700">{{ __('Subject') }}</label>
                                <input type="text" name="subject" id="contact_subject" value="{{ old('subject') }}" required maxlength="200" autocomplete="off"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact_message" class="block text-sm font-semibold text-slate-700">{{ __('Message') }}</label>
                                <textarea name="message" id="contact_message" rows="6" required maxlength="5000"
                                          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <x-primary-button>{{ __('Send message') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- Volunteer platform help --}}
        <section id="volunteer-support" class="mt-16 border-t border-slate-200 pt-16" aria-labelledby="support-form-heading">
            <h2 id="support-form-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('Help and support') }}</h2>
            <p class="mt-3 max-w-3xl text-slate-600 leading-relaxed">{{ __('Support intro') }}</p>
            <p class="mt-3 text-sm text-slate-500">
                <a href="mailto:{{ config('swaeduae.mail.support') }}" class="font-semibold text-emerald-800 hover:underline">{{ config('swaeduae.mail.support') }}</a>
            </p>

            <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-4 lg:pt-2">
                    <p class="text-sm text-slate-600">{{ __('Support form aside') }}</p>
                </div>
                <div class="lg:col-span-8">
                    <div class="card-surface p-6 sm:p-8">
                        <form method="post" action="{{ route('support.store', $localeQ) }}" class="relative space-y-6">
                            @csrf
                            <div class="pointer-events-none absolute -left-[9999px] top-0 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                                <label for="support_trap" class="sr-only">{{ __('Leave this field empty') }}</label>
                                <input type="text" name="support_trap" id="support_trap" value="" tabindex="-1" autocomplete="off">
                            </div>
                            <div>
                                <label for="topic" class="block text-sm font-semibold text-slate-700">{{ __('Support topic label') }}</label>
                                <select name="topic" id="topic" required data-testid="support-topic-select"
                                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="login" @selected($supportTopicValue === 'login')>{{ __('Support topic login') }}</option>
                                    <option value="registration" @selected($supportTopicValue === 'registration')>{{ __('Support topic registration') }}</option>
                                    <option value="attendance" @selected($supportTopicValue === 'attendance')>{{ __('Support topic attendance') }}</option>
                                    <option value="organization" @selected($supportTopicValue === 'organization')>{{ __('Support topic organization account') }}</option>
                                    <option value="certificate" @selected($supportTopicValue === 'certificate')>{{ __('Support topic certificate') }}</option>
                                    <option value="other" @selected($supportTopicValue === 'other')>{{ __('Support topic other') }}</option>
                                </select>
                                @error('topic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="support_name" class="block text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                                <input type="text" name="name" id="support_name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="support_email" class="block text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                                <input type="email" name="email" id="support_email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="support_phone" class="block text-sm font-semibold text-slate-700">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                                <input type="text" name="phone" id="support_phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="support_subject" class="block text-sm font-semibold text-slate-700">{{ __('Subject') }}</label>
                                <input type="text" name="subject" id="support_subject" value="{{ old('subject') }}" required maxlength="200" autocomplete="off"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="support_message" class="block text-sm font-semibold text-slate-700">{{ __('Message') }}</label>
                                <textarea name="message" id="support_message" rows="6" required maxlength="5000"
                                          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <x-primary-button>{{ __('Send message') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <p class="mt-14 border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="contact-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </div>
</x-public-layout>
