@php
    $pageTitle = __('FAQ').' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
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
        </div>
    </div>
</x-public-layout>
