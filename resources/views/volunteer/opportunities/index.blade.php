@php
    $pageTitle = __('Volunteer opportunities').' — '.config('app.name');
    $metaDescription = __('Volunteer opportunities intro');
    $localeQ = \App\Support\PublicLocale::query();
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Volunteer'), 'url' => route('volunteer.index', $localeQ, true)],
        ['name' => __('Volunteer opportunities'), 'url' => route('volunteer.opportunities.index', $localeQ, true)],
    ];
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$metaDescription" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="public-page-title">{{ __('Volunteer opportunities') }}</h1>
                <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('Volunteer opportunities intro') }}</p>
            </div>
            <a href="{{ route('volunteer.index', $localeQ) }}" class="shrink-0 text-sm font-bold text-emerald-800 hover:underline">{{ __('Volunteer hub') }} →</a>
        </div>

        <form method="get" action="{{ route('volunteer.opportunities.index') }}" class="card-surface mt-10 flex flex-col gap-4 p-4 sm:flex-row sm:flex-wrap sm:items-end">
            @foreach ($localeQ as $lk => $lv)
                <input type="hidden" name="{{ $lk }}" value="{{ $lv }}">
            @endforeach
            <div class="min-w-0 flex-1">
                <label for="opp_q" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Search opportunities') }}</label>
                <input type="search" id="opp_q" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('Search by title or host organization') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="opp_sort" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Sort') }}</label>
                <select id="opp_sort" name="sort" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-52">
                    <option value="starts_soon" @selected($sort === 'starts_soon')>{{ __('Starting soonest') }}</option>
                    <option value="starts_late" @selected($sort === 'starts_late')>{{ __('Starting latest') }}</option>
                </select>
            </div>
            <div>
                <label for="opp_entry" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('How to join') }}</label>
                <select id="opp_entry" name="entry" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-52">
                    <option value="all" @selected($entry === 'all')>{{ __('All opportunities') }}</option>
                    <option value="open" @selected($entry === 'open')>{{ __('Open roster only') }}</option>
                    <option value="application" @selected($entry === 'application')>{{ __('Requires application only') }}</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn-primary-solid">{{ __('Apply') }}</button>
                <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="btn-secondary-muted">{{ __('Clear') }}</a>
            </div>
        </form>

        @if ($events->total() === 0)
            <div class="card-dashed-placeholder mt-12">
                @if (filled($search) && $entry === 'all')
                    <p class="font-display font-semibold text-slate-800">{{ __('No opportunities match your search.') }}</p>
                    <a href="{{ route('volunteer.opportunities.index', array_merge($localeQ, array_filter(['sort' => $sort !== 'starts_soon' ? $sort : null]))) }}" class="btn-secondary-muted mt-6 inline-flex">{{ __('Clear') }}</a>
                @elseif (filled($search) || $entry !== 'all')
                    <p class="font-display font-semibold text-slate-800">{{ __('No opportunities match your filters.') }}</p>
                    <a href="{{ route('volunteer.opportunities.index', array_merge($localeQ, array_filter(['sort' => $sort !== 'starts_soon' ? $sort : null]))) }}" class="btn-secondary-muted mt-6 inline-flex">{{ __('Clear') }}</a>
                @else
                    <p class="font-display font-semibold text-slate-800">{{ __('No open opportunities right now') }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Check back soon or contact us to get involved.') }}</p>
                    <a href="{{ route('contact.show') }}" class="btn-primary-solid mt-6">{{ __('Contact') }}</a>
                @endif
            </div>
        @else
            <ul class="mt-10 space-y-4">
                @foreach ($events as $ev)
                    <li class="card-surface p-6">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-display text-lg font-bold text-slate-900">{{ $ev->titleForLocale() }}</h2>
                                    @if ($ev->application_required)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900">{{ __('Requires application') }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $ev->organization?->nameForLocale() }}</p>
                                <p class="mt-3 text-sm text-slate-600">
                                    <span class="font-semibold text-slate-700">{{ __('Schedule') }}:</span>
                                    {{ $ev->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                    — {{ $ev->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                </p>
                                @if ($ev->capacity !== null)
                                    <p class="mt-2 text-sm text-slate-600">
                                        @if ($ev->volunteers_count >= $ev->capacity)
                                            <span class="font-semibold text-amber-800">{{ __('Roster full') }}</span>
                                        @else
                                            {{ __(':current of :capacity spots taken', ['current' => $ev->volunteers_count, 'capacity' => $ev->capacity]) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('volunteer.opportunities.show', array_merge(['event' => $ev], $localeQ)) }}" class="btn-primary-solid shrink-0 text-center">{{ __('View details') }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-10">
                {{ $events->links('vendor.pagination.tailwind-public') }}
            </div>
        @endif
    </div>
</x-public-layout>
