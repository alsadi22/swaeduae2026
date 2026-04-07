@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('About and leadership').' — '.__('SwaedUAE');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('About and leadership'), route('about', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('About and leadership') }}</h1>
            <p class="mt-8 text-lg leading-relaxed text-slate-600">{{ __('site.about_snapshot') }}</p>
            <div class="mt-6">
                <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="about-copy-page-url" />
            </div>
        </div>

        <div class="mt-12 max-w-3xl space-y-8 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm ring-1 ring-slate-100 sm:p-10">
            <div>
                <h2 class="font-display text-lg font-bold text-emerald-950">{{ __('Mission') }}</h2>
                <p class="mt-3 text-slate-600 leading-relaxed">{{ __('Mission placeholder') }}</p>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-emerald-950">{{ __('Vision') }}</h2>
                <p class="mt-3 text-slate-600 leading-relaxed">{{ __('Vision placeholder') }}</p>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-emerald-950">{{ __('Values') }}</h2>
                <p class="mt-3 text-slate-600 leading-relaxed">{{ __('Values placeholder') }}</p>
            </div>
        </div>

        @include('public.partials.leadership-section', ['localeQ' => $localeQ])

        <p class="mt-12 border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="about-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </div>
</x-public-layout>
