@php
    $pageTitle = __('Media center').' — '.__('SwaedUAE');
    $qParams = $search !== '' ? ['q' => $search] : [];
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.meta_description')">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Media center') }}</h1>
        <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('site.media_hub_intro') }}</p>
        <p class="mt-4 max-w-2xl text-sm text-slate-500">{{ __('site.media_hub_list_hint') }}</p>

        <form method="get" action="{{ route('media.index') }}" class="mt-8 flex max-w-xl flex-col gap-3 sm:flex-row sm:items-end" role="search">
            <input type="hidden" name="filter" value="{{ $filter }}">
            @if ($sourceId)
                <input type="hidden" name="source_id" value="{{ $sourceId }}">
            @endif
            <div class="min-w-0 flex-1">
                <label for="media_hub_q" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Search') }}</label>
                <input id="media_hub_q" type="search" name="q" value="{{ old('q', $search) }}" maxlength="120"
                    placeholder="{{ __('site.media_hub_search_placeholder') }}"
                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" />
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-900">{{ __('Search') }}</button>
                @if ($search !== '')
                    <a href="{{ route('media.index', array_filter(['filter' => $filter, 'source_id' => $sourceId])) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear search') }}</a>
                @endif
            </div>
        </form>
        @error('q')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="mt-10 flex flex-wrap gap-2">
            <a href="{{ route('media.index', array_merge(['filter' => 'all'], $qParams)) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'all' && ! $sourceId ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('All updates') }}</a>
            <a href="{{ route('media.index', array_merge(['filter' => 'internal'], $qParams)) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'internal' ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('Our news') }}</a>
            <a href="{{ route('media.index', array_merge(['filter' => 'external'], $qParams)) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'external' ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('External updates') }}</a>
        </div>

        @if ($sources->isNotEmpty())
            <form method="get" action="{{ route('media.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                @if ($search !== '')
                    <input type="hidden" name="q" value="{{ $search }}">
                @endif
                <div>
                    <label for="hub_source" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Source') }}</label>
                    <select id="hub_source" name="source_id" class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All sources') }}</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src->id }}" {{ (int) $sourceId === (int) $src->id ? 'selected' : '' }}>{{ $src->labelForLocale() }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($internalNews as $page)
                <article class="card-surface flex flex-col overflow-hidden">
                    <div class="h-32 bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50"></div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ __('Our article') }}</p>
                        <h2 class="font-display mt-2 font-bold text-slate-900">{{ $page->title }}</h2>
                        @if ($page->excerpt)
                            <p class="mt-2 text-sm text-slate-600">{{ $page->excerpt }}</p>
                        @endif
                        <div class="mt-auto pt-4">
                            <a href="{{ $page->publicUrl() }}?lang={{ app()->getLocale() }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Read more') }} →</a>
                        </div>
                    </div>
                </article>
            @endforeach

            @foreach ($externalNews as $ext)
                <article class="card-surface flex flex-col overflow-hidden ring-1 ring-amber-100/80">
                    @if ($ext->featureImageUrl())
                        <div class="h-32 overflow-hidden bg-slate-100">
                            <img src="{{ $ext->featureImageUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy" width="400" height="200">
                        </div>
                    @else
                        <div class="h-32 bg-gradient-to-br from-slate-100 to-amber-50"></div>
                    @endif
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('External source') }} · {{ $ext->source->labelForLocale() }}</p>
                        <h2 class="font-display mt-2 font-bold text-slate-900">{{ $ext->titleForLocale() }}</h2>
                        @if ($ext->summaryForLocale())
                            <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($ext->summaryForLocale(), 180) }}</p>
                        @endif
                        @if ($ext->original_published_at)
                            <p class="mt-2 text-xs text-slate-500">{{ __('Original date') }}: {{ $ext->original_published_at->locale(app()->getLocale())->isoFormat('LL') }}</p>
                        @endif
                        <div class="mt-auto flex flex-wrap gap-3 pt-4">
                            <a href="{{ $ext->publicDetailUrl() }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('View details') }}</a>
                            @if ($ext->external_url)
                                <a href="{{ $ext->external_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-slate-600 hover:text-emerald-900">{{ __('Visit source') }} →</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($internalNews->isEmpty() && $externalNews->isEmpty())
            <p class="mt-12 text-slate-600">{{ $searchActive ? __('site.media_hub_search_empty') : __('site.media_hub_empty') }}</p>
        @endif
    </div>
</x-public-layout>
