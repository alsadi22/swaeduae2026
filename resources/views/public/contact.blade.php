@php
    $pageTitle = __('Contact').' — '.__('SwaedUAE');
    $localeQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Contact'), 'url' => route('contact.show', $localeQ, true)],
    ];
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Contact') }}</h1>

        <div class="mt-10 grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-4">
                <p class="text-slate-600 leading-relaxed">{{ __('Contact intro') }}</p>
                <p class="mt-3 text-sm text-slate-600">{{ __('Contact routes to info') }}</p>
                <p class="mt-4 text-sm text-slate-500">
                    <a href="mailto:{{ config('swaeduae.mail.info') }}" class="font-semibold text-emerald-800 hover:underline">{{ config('swaeduae.mail.info') }}</a>
                </p>
                <p class="mt-6 text-sm text-slate-600">{{ __('Contact also see support') }}</p>
                <p class="mt-2 text-sm text-slate-500">
                    <a href="{{ route('support.show', $localeQ) }}" class="font-semibold text-emerald-800 hover:underline">{{ __('Help and support') }}</a>
                    <span class="text-slate-500"> · </span>
                    <a href="mailto:{{ config('swaeduae.mail.support') }}" class="font-semibold text-emerald-800 hover:underline">{{ config('swaeduae.mail.support') }}</a>
                </p>
                <p class="mt-6 text-sm text-slate-500">
                    <span class="font-medium text-slate-700">{{ config('swaeduae.domain') }}</span>
                </p>
            </div>

            <div class="lg:col-span-8">
                <div class="card-surface p-6 sm:p-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('contact.store') }}" class="relative space-y-6">
                        @csrf
                        <div class="pointer-events-none absolute -left-[9999px] top-0 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                            <label for="contact_trap" class="sr-only">{{ __('Leave this field empty') }}</label>
                            <input type="text" name="contact_trap" id="contact_trap" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="120"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" maxlength="40"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-semibold text-slate-700">{{ __('Subject') }}</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required maxlength="200"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-semibold text-slate-700">{{ __('Message') }}</label>
                            <textarea name="message" id="message" rows="6" required maxlength="5000"
                                      class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <x-primary-button>{{ __('Send message') }}</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
