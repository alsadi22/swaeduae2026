@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('Terms').' — '.__('SwaedUAE');
    $metaDescription = __('site.terms_intro');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('Terms of use'), route('legal.terms', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$metaDescription" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            @include('public.legal.placeholder-notice')
            <h1 class="public-page-title">{{ __('Terms of use') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.terms_intro') }}</p>
            @include('public.legal.data-rights-strip')
            <p class="mt-12 border-t border-slate-200 pt-10 text-center">
                <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="legal-terms-footer-opportunities">{{ __('Browse opportunities') }} →</a>
            </p>
        </div>
    </div>
</x-public-layout>
