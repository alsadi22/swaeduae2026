@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('FAQ').' — '.__('SwaedUAE');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('FAQ'), route('faq', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('FAQ') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.faq_intro') }}</p>
            <div class="mt-10 space-y-4">
                <details class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm open:ring-2 open:ring-emerald-100">
                    <summary class="cursor-pointer font-display font-semibold text-slate-900">{{ __('FAQ sample question') }}</summary>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ __('FAQ sample answer') }}</p>
                </details>
            </div>
            <p class="mt-12 border-t border-slate-200 pt-10 text-center">
                <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="faq-footer-opportunities">{{ __('Browse opportunities') }} →</a>
            </p>
        </div>
    </div>
</x-public-layout>
