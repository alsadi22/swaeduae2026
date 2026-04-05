@php
    $pageTitle = __('Programs').' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Programs & initiatives') }}</h1>
        <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('site.programs_intro') }}</p>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (range(1, 6) as $i)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="mb-4 h-32 rounded-lg bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50"></div>
                    <h2 class="font-display font-bold text-slate-900">{{ __('Initiative') }} {{ $i }}</h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ __('Placeholder card body') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</x-public-layout>
