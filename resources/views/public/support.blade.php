@php
    $pageTitle = __('Help and support').' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Help and support') }}</h1>

        <div class="mt-10 grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-4">
                <p class="text-slate-600 leading-relaxed">{{ __('Support intro') }}</p>
                <p class="mt-4 text-sm text-slate-500">
                    <a href="mailto:{{ config('swaeduae.mail.support') }}" class="font-semibold text-emerald-800 hover:underline">{{ config('swaeduae.mail.support') }}</a>
                </p>
                <p class="mt-6 text-sm text-slate-600">
                    <a href="{{ route('contact.show') }}" class="font-semibold text-emerald-800 hover:underline">{{ __('Contact') }}</a>
                    <span class="text-slate-500"> — {{ __('General inquiries and partnerships') }}</span>
                </p>
            </div>

            <div class="lg:col-span-8">
                <div class="card-surface p-6 sm:p-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('support.store') }}" class="relative space-y-6">
                        @csrf
                        <div class="pointer-events-none absolute -left-[9999px] top-0 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                            <label for="support_trap" class="sr-only">{{ __('Leave this field empty') }}</label>
                            <input type="text" name="support_trap" id="support_trap" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div>
                            <label for="topic" class="block text-sm font-semibold text-slate-700">{{ __('Support topic label') }}</label>
                            <select name="topic" id="topic" required
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="login" @selected(old('topic') === 'login')>{{ __('Support topic login') }}</option>
                                <option value="registration" @selected(old('topic') === 'registration')>{{ __('Support topic registration') }}</option>
                                <option value="attendance" @selected(old('topic') === 'attendance')>{{ __('Support topic attendance') }}</option>
                                <option value="organization" @selected(old('topic') === 'organization')>{{ __('Support topic organization account') }}</option>
                                <option value="certificate" @selected(old('topic') === 'certificate')>{{ __('Support topic certificate') }}</option>
                                <option value="other" @selected(old('topic', 'other') === 'other')>{{ __('Support topic other') }}</option>
                            </select>
                            @error('topic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
