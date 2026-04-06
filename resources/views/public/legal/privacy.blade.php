@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('Privacy').' — '.__('SwaedUAE');
    $metaDescription = __('site.privacy_intro');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('Privacy policy'), route('legal.privacy', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$metaDescription" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            @include('public.legal.placeholder-notice')
            <h1 class="public-page-title">{{ __('Privacy policy') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.privacy_intro') }}</p>
        </div>
    </div>
</x-public-layout>
