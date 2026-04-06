@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('About').' — '.__('SwaedUAE');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('About'), route('about', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('About') }}</h1>
            <p class="mt-8 text-lg leading-relaxed text-slate-600">{{ __('site.about_snapshot') }}</p>
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
        <p class="mt-8 max-w-3xl text-sm text-slate-500">
            <a href="{{ route('leadership') }}" class="font-semibold text-emerald-800 hover:underline">{{ __('Leadership') }}</a>
            <span class="text-slate-400"> — </span>
            {{ __('Leadership page note') }}
        </p>
    </div>
</x-public-layout>
