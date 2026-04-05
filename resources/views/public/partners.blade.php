@php
    $pageTitle = __('Partners').' — '.__('SwaedUAE');
    $pagePartners = config('swaeduae.home_partners', []);
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('Partners') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.partners_intro') }}</p>
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
    </div>
</x-public-layout>
