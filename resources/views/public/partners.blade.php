@php
    $pageTitle = __('Partners').' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('Partners') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.partners_intro') }}</p>
        </div>
        <div class="mt-12 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-500">
            {{ __('site.partners_strip') }}
        </div>
    </div>
</x-public-layout>
