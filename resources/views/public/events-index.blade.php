@php
    use App\Support\SwaedUaeStructuredData;

    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Event> $events */
    /** @var \App\Models\CmsPage|null $cmsPage */
    $eventsLocaleQ = \App\Support\PublicLocale::queryForUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $eventsLocaleQ, true)],
        ['name' => __('Events'), 'url' => route('events.index', $eventsLocaleQ, true)],
    ];
    $eventsCmsOgUrl = $cmsPage !== null ? $cmsPage->absolutePublicUrl() : null;
    $eventsExtraJsonLd = $cmsPage !== null
        ? SwaedUaeStructuredData::cmsArticleForJsonLd($cmsPage, $cmsPage->absolutePublicUrl())
        : null;
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$eventsCmsOgUrl"
    :canonicalUrl="$eventsCmsOgUrl"
    :ogTitle="$cmsPage?->title"
    :ogImage="$cmsPage?->resolvedOgImageUrl()"
    :ogType="$cmsPage !== null ? 'article' : 'website'"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$eventsExtraJsonLd"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        @if ($cmsPage)
            <article class="mx-auto max-w-3xl">
                <header class="border-b border-slate-200 pb-8">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
                    <h1 class="public-page-title mt-3">{{ $cmsPage->title }}</h1>
                    @if ($cmsPage->excerpt)
                        <p class="mt-6 text-lg leading-relaxed text-slate-600">{{ $cmsPage->excerpt }}</p>
                    @endif
                </header>
                @if (filled($cmsPage->body))
                    <div class="cms-body prose prose-slate mt-10 max-w-none prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                        {!! str($cmsPage->body)->markdown() !!}
                    </div>
                @endif
            </article>
        @else
            <h1 class="public-page-title">{{ __('Events') }}</h1>
            <p class="mt-8 max-w-2xl leading-relaxed text-slate-600">{{ __('site.events_intro') }}</p>
        @endif

        <section class="{{ $cmsPage ? 'mt-16 border-t border-slate-200 pt-16' : 'mt-12' }}">
            <h2 class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('Upcoming events') }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">{{ __('site.events_calendar_hint') }}</p>

            <form method="get" action="{{ route('events.index', $eventsLocaleQ) }}" class="card-surface mt-8 flex flex-col gap-4 p-4 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="min-w-0 flex-1">
                    <label for="public_events_q" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Search events') }}</label>
                    <input type="search" id="public_events_q" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('Search by title or host organization') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary-solid">{{ __('Apply') }}</button>
                    @if (filled($search))
                        <a href="{{ route('events.index', $eventsLocaleQ) }}" class="btn-secondary-muted">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>

            @if ($events->total() === 0)
                @if (filled($search))
                    <p class="mt-8 text-sm text-slate-600">{{ __('No events match your search.') }}</p>
                    <a href="{{ route('events.index', $eventsLocaleQ) }}" class="btn-secondary-muted mt-4 inline-flex">{{ __('Clear') }}</a>
                @else
                    <p class="mt-8 text-sm text-slate-600">{{ __('No upcoming events listed yet.') }}</p>
                    <a href="{{ route('volunteer.opportunities.index', $eventsLocaleQ) }}" class="mt-4 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline" data-testid="events-footer-opportunities">{{ __('Browse opportunities') }} →</a>
                @endif
            @else
                <ul class="mt-10 space-y-4">
                    @foreach ($events as $ev)
                        <li class="card-surface p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    @if ($ev->application_required)
                                        <span class="text-sm font-bold text-amber-800">{{ __('Requires application') }}</span>
                                    @else
                                        <span class="text-sm font-bold text-emerald-800">{{ __('Open registration') }}</span>
                                    @endif
                                    <h3 class="font-display mt-2 text-lg font-bold text-slate-900">
                                        <a href="{{ route('events.show', array_merge(['event' => $ev], $eventsLocaleQ)) }}" class="hover:text-emerald-800 hover:underline">{{ $ev->titleForLocale() }}</a>
                                    </h3>
                                    @if ($ev->organization)
                                        <p class="mt-1 text-sm text-slate-500">{{ $ev->organization->nameForLocale() }}</p>
                                    @endif
                                    <p class="mt-2 text-sm text-slate-600">
                                        {{ $ev->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                        <span class="text-slate-400">—</span>
                                        {{ $ev->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                    </p>
                                </div>
                                <a href="{{ route('events.show', array_merge(['event' => $ev], $eventsLocaleQ)) }}" class="btn-primary-solid shrink-0">{{ __('Details') }}</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-10">
                    {{ $events->links('vendor.pagination.tailwind-public') }}
                </div>
                <a href="{{ route('volunteer.opportunities.index', $eventsLocaleQ) }}" class="mt-8 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline" data-testid="events-footer-opportunities">{{ __('Volunteer opportunities') }} →</a>
            @endif
        </section>
    </div>
</x-public-layout>
