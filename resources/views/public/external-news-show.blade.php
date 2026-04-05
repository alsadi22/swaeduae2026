@php
    /** @var \App\Models\ExternalNewsItem $item */
    $pageTitle = $item->titleForLocale().' — '.__('SwaedUAE');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <p class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('External source') }} · {{ $item->source->labelForLocale() }}</p>
        <h1 class="public-page-title mt-4">{{ $item->titleForLocale() }}</h1>
        @if ($item->original_published_at)
            <p class="mt-4 text-sm text-slate-500">{{ __('Originally published') }}: {{ $item->original_published_at->locale(app()->getLocale())->isoFormat('LL') }}</p>
        @endif

        @if ($item->featureImageUrl())
            <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                <img src="{{ $item->featureImageUrl() }}" alt="" class="max-h-[28rem] w-full object-cover" loading="lazy" width="1200" height="630">
            </div>
        @endif

        @if ($item->summaryForLocale())
            <div class="prose prose-slate mt-10 max-w-prose">
                <p class="text-slate-700 leading-relaxed">{{ $item->summaryForLocale() }}</p>
            </div>
        @endif

        <div class="mt-10 rounded-2xl border border-amber-100 bg-amber-50/50 p-6 text-sm text-slate-700">
            <p class="font-semibold text-slate-900">{{ __('Attribution') }}</p>
            <p class="mt-2">{{ __('This summary is shown for your convenience. The association did not author the original piece.') }}</p>
            @if ($item->external_url)
                <a href="{{ $item->external_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex font-bold text-emerald-800 hover:underline">{{ __('Read the full article at the source') }} →</a>
            @endif
        </div>

        <p class="mt-10">
            <a href="{{ route('media.index') }}" class="text-sm font-bold text-emerald-800 hover:underline">← {{ __('Media center') }}</a>
        </p>
    </div>
</x-public-layout>
