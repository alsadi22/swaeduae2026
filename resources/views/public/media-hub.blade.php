@php
    $pageTitle = __('Media center').' — '.__('SwaedUAE');
    $metaDescription = __('site.media_hub_meta_description');
    $hubCanonical = request()->fullUrl();
    $ogImage = \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    $localeQ = \App\Support\PublicLocale::queryForUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Media center'), 'url' => route('media.index', $localeQ, true)],
    ];
    $internalTotal = $internalPaginator?->total() ?? 0;
    $externalTotal = $externalPaginator?->total() ?? 0;
    $bothEmpty = $internalTotal === 0 && $externalTotal === 0;
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$hubCanonical"
    :canonicalUrl="$hubCanonical"
    :ogTitle="$pageTitle"
    :ogDescription="$metaDescription"
    :ogImage="$ogImage"
    :breadcrumbItems="$breadcrumbItems"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="public-page-title">{{ __('Media center') }}</h1>
        <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('site.media_hub_intro') }}</p>
        <p class="mt-4 max-w-2xl text-sm text-slate-500">{{ __('site.media_hub_list_hint') }}</p>
        <p class="mt-4 max-w-2xl text-sm text-slate-600">
            <a href="{{ route('feed', $localeQ) }}" class="font-bold text-emerald-800 hover:text-emerald-950 hover:underline">{{ __('News feed') }}</a>
            <span class="text-slate-400"> · </span>
            <span class="text-slate-500">{{ __('site.media_hub_feed_hint') }}</span>
        </p>

        <form method="get" action="{{ route('media.index', $localeQ) }}" class="mt-8 flex max-w-xl flex-col gap-3 sm:flex-row sm:items-end" role="search">
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
                    <a href="{{ route('media.index', array_merge($localeQ, array_filter(['filter' => $filter, 'source_id' => $sourceId]))) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear search') }}</a>
                @endif
            </div>
        </form>
        @error('q')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="mt-10 flex flex-wrap gap-2">
            <a href="{{ route('media.index', array_merge($localeQ, ['filter' => 'all'], $search !== '' ? ['q' => $search] : [])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'all' && ! $sourceId ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('All updates') }}</a>
            <a href="{{ route('media.index', array_merge($localeQ, ['filter' => 'internal'], $search !== '' ? ['q' => $search] : [])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'internal' ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('Our news') }}</a>
            <a href="{{ route('media.index', array_merge($localeQ, ['filter' => 'external'], $search !== '' ? ['q' => $search] : [])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $filter === 'external' ? 'bg-emerald-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('External updates') }}</a>
        </div>

        @if ($sources->isNotEmpty())
            <form method="get" action="{{ route('media.index', $localeQ) }}" class="mt-6 flex flex-wrap items-end gap-3">
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

        @if ($bothEmpty)
            <p class="mt-12 text-slate-600">{{ $searchActive ? __('site.media_hub_search_empty') : __('site.media_hub_empty') }}</p>
        @elseif ($filter === 'all')
            @if ($internalTotal > 0)
                <section class="mt-12" aria-labelledby="media-hub-internal-heading">
                    <h2 id="media-hub-internal-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('Our news') }}</h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @include('public.partials.media-hub-internal-cards', ['pages' => $internalPaginator])
                    </div>
                    @if ($internalPaginator->hasPages())
                        <div class="mt-10">
                            {{ $internalPaginator->links('vendor.pagination.tailwind-public') }}
                        </div>
                    @endif
                </section>
            @endif
            @if ($externalTotal > 0)
                <section class="@if($internalTotal > 0) mt-16 border-t border-slate-200 pt-14 @else mt-12 @endif" aria-labelledby="media-hub-external-heading">
                    <h2 id="media-hub-external-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('External updates') }}</h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @include('public.partials.media-hub-external-cards', ['items' => $externalPaginator])
                    </div>
                    @if ($externalPaginator->hasPages())
                        <div class="mt-10">
                            {{ $externalPaginator->links('vendor.pagination.tailwind-public') }}
                        </div>
                    @endif
                </section>
            @endif
        @elseif ($filter === 'internal')
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @include('public.partials.media-hub-internal-cards', ['pages' => $internalPaginator])
            </div>
            @if ($internalPaginator->hasPages())
                <div class="mt-10">
                    {{ $internalPaginator->links('vendor.pagination.tailwind-public') }}
                </div>
            @endif
        @else
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @include('public.partials.media-hub-external-cards', ['items' => $externalPaginator])
            </div>
            @if ($externalPaginator->hasPages())
                <div class="mt-10">
                    {{ $externalPaginator->links('vendor.pagination.tailwind-public') }}
                </div>
            @endif
        @endif

        <p class="mt-12 border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="media-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </div>
</x-public-layout>
