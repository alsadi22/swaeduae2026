@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('Partners').' — '.__('SwaedUAE');
    $pagePartners = config('swaeduae.home_partners', []);
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('Partners'), route('partners', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('Partners') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.partners_intro') }}</p>
            <div class="mt-6">
                <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="partners-copy-page-url" />
            </div>
        </div>

        @if (count($pagePartners) > 0)
            <section class="mt-12 rounded-2xl border border-slate-200/90 bg-white px-6 py-10 shadow-sm ring-1 ring-slate-100 sm:px-10" aria-label="{{ __('Partners') }}">
                <x-public-partners-logo-grid class="mt-2" :partners="$pagePartners" />
            </section>
        @else
            <div class="mt-12 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-500">
                {{ __('site.partners_strip') }}
            </div>
        @endif
        <p class="mt-12 border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="partners-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </div>
</x-public-layout>
