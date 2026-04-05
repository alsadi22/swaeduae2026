@php
    $pageTitle = __('SwaedUAE').' — '.__('Home');
    $metaDescription = __('site.meta_description');
    $ogImage = \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="url('/')"
    :ogTitle="$pageTitle"
    :ogDescription="$metaDescription"
    :ogImage="$ogImage"
>
    {{-- Hero: deep institutional band (EASD / MOCE-inspired clarity) --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-emerald-950 to-teal-950 text-white">
        <div class="absolute inset-0 opacity-[0.12]" aria-hidden="true" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.35\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-content px-4 py-14 sm:px-6 sm:py-20 lg:py-24">
            <p class="max-w-3xl text-sm font-semibold uppercase tracking-[0.2em] text-emerald-200/90">{{ __('SwaedUAE Association for Culture and Community Empowerment') }}</p>
            <h1 class="font-display mt-5 max-w-4xl text-balance text-3xl font-bold leading-[1.15] tracking-tight sm:text-4xl lg:text-5xl">
                {{ __('site.hero_mission') }}
            </h1>
            <p class="mt-6 max-w-3xl text-base leading-relaxed text-emerald-100/95 sm:text-lg">
                {{ __('site.hero_subline') }}
            </p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('programs.index') }}" class="btn-hero-solid">{{ __('Explore programs') }}</a>
                <a href="{{ route('youth-councils') }}" class="btn-hero-ghost">{{ __('Youth Councils') }}</a>
                <a href="{{ route('volunteer.index') }}" class="btn-hero-ghost">{{ __('Become a volunteer') }}</a>
                <a href="{{ route('events.index') }}" class="btn-hero-outline">{{ __('View events') }}</a>
                <a href="{{ route('contact.show') }}" class="btn-hero-outline">{{ __('Partner with us') }}</a>
            </div>
        </div>
    </section>

    {{-- MOCE-style "Start here" quick access row --}}
    <section class="border-b border-slate-200 bg-white" aria-labelledby="start-here-heading">
        <div class="mx-auto max-w-content px-4 py-10 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h2 id="start-here-heading" class="font-display text-sm font-bold uppercase tracking-widest text-slate-500">{{ __('Start here') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Programs & events') }}</p>
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('programs.index') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Programs') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Programs card hint') }}</span>
                    </span>
                </a>
                <a href="{{ route('events.index') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Events') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Events card hint') }}</span>
                    </span>
                </a>
                <a href="{{ route('youth-councils') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Youth Councils') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Youth Councils card hint') }}</span>
                    </span>
                </a>
                <a href="{{ route('volunteer.index') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Volunteer') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Volunteer card hint') }}</span>
                    </span>
                </a>
                <a href="{{ route('media.index') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Media center') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Media center card hint') }}</span>
                    </span>
                </a>
                <a href="{{ route('contact.show') }}" class="card-interactive group flex gap-4">
                    <span class="card-icon-tile" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="min-w-0 text-start">
                        <span class="block font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ __('Contact') }}</span>
                        <span class="mt-0.5 block text-sm text-slate-600">{{ __('Contact card hint') }}</span>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-content space-y-20 px-4 py-16 sm:px-6 sm:py-20">
        {{-- About snapshot --}}
        <section class="card-surface p-8 sm:p-10">
            <h2 class="public-section-title">{{ __('About') }}</h2>
            <p class="mt-8 max-w-3xl text-slate-600 leading-relaxed">{{ __('site.about_snapshot') }}</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('about') }}" class="inline-flex text-sm font-bold text-emerald-800 hover:text-emerald-950">{{ __('Read more') }} →</a>
                <a href="{{ route('cms.page', ['slug' => 'community-charter']) }}" class="inline-flex text-sm font-bold text-slate-600 hover:text-emerald-900">{{ __('Community charter') }} →</a>
            </div>
        </section>

        {{-- Pillars --}}
        <section>
            <h2 class="public-section-title public-section-title--center mx-auto w-fit max-w-full text-center">{{ __('Strategic pillars') }}</h2>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (trans('site.pillars') as $pillar)
                    <div class="card-surface p-6 transition duration-200 hover:shadow-card-hover">
                        <h3 class="font-display font-bold text-emerald-950">{{ $pillar['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $pillar['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Featured from CMS --}}
        <section>
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <h2 class="public-section-title">{{ __('Featured programs') }}</h2>
                <a href="{{ route('programs.index') }}" class="shrink-0 text-sm font-bold text-emerald-800 hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredCmsPages as $fp)
                    <article class="card-surface overflow-hidden transition duration-200 hover:shadow-card-hover">
                        <div class="h-36 bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50"></div>
                        <div class="p-5">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ __('From the CMS') }}</p>
                            <h3 class="font-display mt-2 font-bold text-slate-900">{{ $fp->title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $fp->excerpt ?? \Illuminate\Support\Str::limit($fp->body, 160) }}</p>
                            <a href="{{ $fp->publicUrl() }}?lang={{ app()->getLocale() }}" class="mt-4 inline-flex text-sm font-bold text-emerald-800 hover:underline">{{ __('Read more') }} →</a>
                        </div>
                    </article>
                @empty
                    @foreach (range(1, 3) as $i)
                        <article class="card-surface overflow-hidden transition duration-200 hover:shadow-card-hover">
                            <div class="h-36 bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50"></div>
                            <div class="p-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ __('Program') }} {{ $i }}</p>
                                <h3 class="font-display mt-2 font-bold text-slate-900">{{ __('Coming soon') }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ __('CMS-driven program cards will appear here.') }}</p>
                            </div>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </section>

        {{-- Volunteer highlight --}}
        <section class="rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-white to-teal-50/40 p-8 shadow-card sm:p-10 ring-1 ring-emerald-100/80">
            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <h2 class="public-section-title">{{ __('Volunteer platform') }}</h2>
                    <p class="mt-8 text-slate-700 leading-relaxed">{{ __('site.volunteer_highlight') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('volunteer.index') }}" class="btn-primary-solid">{{ __('Open volunteer hub') }}</a>
                        @guest
                            <a href="{{ route('register') }}" class="btn-secondary-muted">{{ __('Create account') }}</a>
                        @endguest
                    </div>
                </div>
                <ol class="space-y-4 text-sm text-slate-700">
                    <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white">1</span> {{ __('Volunteer step register') }}</li>
                    <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white">2</span> {{ __('Volunteer step apply') }}</li>
                    <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white">3</span> {{ __('Volunteer step attend') }}</li>
                </ol>
            </div>
        </section>

        {{-- Upcoming events --}}
        <section>
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <h2 class="public-section-title">{{ __('Upcoming events') }}</h2>
                <a href="{{ route('events.index') }}" class="shrink-0 text-sm font-bold text-emerald-800 hover:underline">{{ __('Calendar') }}</a>
            </div>
            <ul class="mt-10 space-y-4">
                @forelse ($upcomingEvents as $ev)
                    <li class="card-surface flex flex-wrap items-center justify-between gap-4 px-5 py-4" data-testid="home-upcoming-event">
                        <div class="min-w-0">
                            @if ($ev->application_required)
                                <span class="inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900">{{ __('Requires application') }}</span>
                            @else
                                <span class="inline-block rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-900">{{ __('Open registration') }}</span>
                            @endif
                            <p class="mt-2 font-display font-semibold text-slate-900">{{ $ev->titleForLocale() }}</p>
                            @if ($ev->organization)
                                <p class="text-sm text-slate-500">{{ $ev->organization->nameForLocale() }}</p>
                            @endif
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $ev->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                <span class="text-slate-400">—</span>
                                {{ $ev->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}
                            </p>
                        </div>
                        <a href="{{ route('events.show', $ev) }}" class="shrink-0 text-sm font-bold text-emerald-800 hover:text-emerald-950 hover:underline">{{ __('Details') }}</a>
                    </li>
                @empty
                    <li class="card-surface px-5 py-4">
                        <p class="text-sm text-slate-600">{{ __('No upcoming events listed yet.') }}</p>
                        <div class="mt-3 flex flex-wrap gap-4">
                            <a href="{{ route('events.index') }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Calendar') }} →</a>
                            <a href="{{ route('volunteer.opportunities.index') }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Browse opportunities') }} →</a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </section>

        {{-- Impact: EASD-style stat tiles on light panel --}}
        <section class="rounded-2xl border border-slate-200/90 bg-gradient-to-b from-slate-100/90 to-surface-page px-6 py-12 shadow-inner sm:px-10 ring-1 ring-slate-200/50">
            <h2 class="public-section-title public-section-title--center mx-auto w-fit max-w-full text-center">{{ __('Impact') }}</h2>
            <p class="mx-auto mt-6 max-w-2xl text-center text-sm text-slate-600">{{ __('site.impact_intro') }}</p>
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5">
                <div class="card-surface px-4 py-6 text-center transition duration-200 hover:shadow-card-hover">
                    <p class="font-display text-3xl font-bold text-emerald-900" data-testid="impact-stat-hours">{{ $impactStats['hours_display'] }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Verified volunteer hours') }}</p>
                </div>
                <div class="card-surface px-4 py-6 text-center transition duration-200 hover:shadow-card-hover">
                    <p class="font-display text-3xl font-bold text-emerald-900" data-testid="impact-stat-volunteers">{{ number_format($impactStats['volunteers_count']) }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Volunteers with verified time') }}</p>
                </div>
                <div class="card-surface px-4 py-6 text-center transition duration-200 hover:shadow-card-hover">
                    <p class="font-display text-3xl font-bold text-emerald-900" data-testid="impact-stat-partners">{{ number_format($impactStats['partners_count']) }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Host organizations') }}</p>
                </div>
                <div class="card-surface px-4 py-6 text-center transition duration-200 hover:shadow-card-hover">
                    <p class="font-display text-3xl font-bold text-emerald-900" data-testid="impact-stat-events">{{ number_format($impactStats['events_with_verified_time_count']) }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Events with verified attendance') }}</p>
                </div>
            </div>
        </section>

        {{-- Latest news --}}
        <section>
            <h2 class="public-section-title">{{ __('Latest news') }}</h2>
            @if ($homeNewsTeasers->isEmpty())
                <p class="mt-8 text-slate-600">{{ __('site.news_placeholder') }}</p>
            @else
                <ul class="mt-8 space-y-4">
                    @foreach ($homeNewsTeasers as $row)
                        @if ($row['kind'] === 'internal')
                            @php $item = $row['page']; @endphp
                            <li class="card-surface p-5 transition duration-200 hover:shadow-card-hover">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ __('Our article') }}</span>
                                <a href="{{ $item->publicUrl() }}?lang={{ app()->getLocale() }}" class="mt-1 block font-display font-bold text-emerald-900 hover:underline">{{ $item->title }}</a>
                                @if ($item->excerpt)
                                    <p class="mt-2 text-sm text-slate-600">{{ $item->excerpt }}</p>
                                @endif
                            </li>
                        @else
                            @php $ext = $row['item']; @endphp
                            <li class="card-surface p-5 transition duration-200 hover:shadow-card-hover ring-1 ring-amber-100/80">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('External source') }} · {{ $ext->source->labelForLocale() }}</span>
                                <a href="{{ $ext->publicDetailUrl() }}" class="mt-1 block font-display font-bold text-emerald-900 hover:underline">{{ $ext->titleForLocale() }}</a>
                                @if ($ext->summaryForLocale())
                                    <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($ext->summaryForLocale(), 200) }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-4 text-sm font-bold">
                                    <a href="{{ $ext->publicDetailUrl() }}" class="text-emerald-800 hover:underline">{{ __('View details') }}</a>
                                    @if ($ext->external_url)
                                        <a href="{{ $ext->external_url }}" target="_blank" rel="noopener noreferrer" class="text-slate-600 hover:text-emerald-900">{{ __('Visit source') }} →</a>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
            <a href="{{ route('media.index') }}" class="mt-6 inline-flex text-sm font-bold text-emerald-800 hover:underline">{{ __('Media center') }} →</a>
        </section>

        {{-- Partners strip --}}
        <section class="card-dashed-placeholder">
            {{ __('site.partners_strip') }}
        </section>

        {{-- Gallery preview --}}
        <section>
            <h2 class="public-section-title">{{ __('Gallery') }}</h2>
            <p class="mt-8 text-slate-600">{{ __('site.gallery_placeholder') }}</p>
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach (range(1, 4) as $i)
                    <div class="aspect-square rounded-xl bg-gradient-to-br from-slate-200 via-slate-100 to-emerald-100 ring-1 ring-slate-200/80"></div>
                @endforeach
            </div>
        </section>
    </div>
</x-public-layout>
