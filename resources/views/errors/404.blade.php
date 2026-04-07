@php
    $pageTitle = __('Page not found').' — '.__('SwaedUAE');
    $localeQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-16 sm:px-6 sm:py-20">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
        <h1 class="public-page-title mt-3">{{ __('Page not found') }}</h1>
        <p class="mt-6 max-w-xl text-slate-600 leading-relaxed">{{ __('The page you are looking for does not exist or may have been moved.') }}</p>
        <div class="mt-6">
            <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="error-404-copy-page-url" />
        </div>
        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('home', $localeQ) }}" class="btn-primary-solid">{{ __('Back to home') }}</a>
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="btn-secondary-muted" data-testid="error-404-opportunities">{{ __('Browse opportunities') }}</a>
            <a href="{{ route('events.index', $localeQ) }}" class="btn-secondary-muted" data-testid="error-404-events">{{ __('Events') }}</a>
            <a href="{{ route('contact.show', array_merge($localeQ, ['topic' => 'other'])) }}" class="btn-secondary-muted" data-testid="error-404-support">{{ __('Contact and support') }}</a>
        </div>
    </div>
</x-public-layout>
