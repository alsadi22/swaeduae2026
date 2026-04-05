@php
    $pageTitle = __('Media').' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Media center') }}</h1>
        <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('site.media_intro') }}</p>
        <div class="mt-12 grid gap-8 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100 lg:col-span-2">
                <h2 class="font-display text-lg font-bold text-emerald-950">{{ __('News') }}</h2>
                <div class="mt-2 h-1 w-12 rounded-full bg-institution-gold" aria-hidden="true"></div>
                <p class="mt-6 text-sm text-slate-600 leading-relaxed">{{ __('site.news_placeholder') }}</p>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <h2 class="font-display text-lg font-bold text-emerald-950">{{ __('Reports') }}</h2>
                <div class="mt-2 h-1 w-12 rounded-full bg-institution-gold" aria-hidden="true"></div>
                <p class="mt-6 text-sm text-slate-600 leading-relaxed">{{ __('Reports placeholder') }}</p>
            </section>
        </div>
    </div>
</x-public-layout>
