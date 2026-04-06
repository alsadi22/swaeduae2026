@php
    /** @var \App\Models\Event $event */
    $ogImage = \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    $eventShowLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $eventShowLocaleQ, true)],
        ['name' => __('Events'), 'url' => route('events.index', $eventShowLocaleQ, true)],
        ['name' => \Illuminate\Support\Str::limit($event->titleForLocale(), 72), 'url' => route('events.show', array_merge(['event' => $event], $eventShowLocaleQ), true)],
    ];
    $extraJsonLd = \App\Support\SwaedUaeStructuredData::publicEventForJsonLd($event);
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="route('events.show', array_merge(['event' => $event], $eventShowLocaleQ), true)"
    :ogTitle="$event->titleForLocale()"
    :ogDescription="$metaDescription"
    ogType="article"
    :ogImage="$ogImage"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$extraJsonLd"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <a href="{{ route('events.index', $eventShowLocaleQ) }}" class="text-sm font-bold text-emerald-800 hover:underline">← {{ __('Events') }}</a>

        <article class="mt-8 max-w-3xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm ring-1 ring-slate-100">
            @if ($isPast)
                <p class="mb-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800">{{ __('Past event') }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-6">
                <h1 class="font-display text-2xl font-bold text-emerald-950 sm:text-3xl">{{ $event->titleForLocale() }}</h1>
                @if ($event->application_required)
                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900">{{ __('Requires application') }}</span>
                @endif
            </div>

            @if ($event->organization)
                <p class="mt-2 text-sm text-slate-500">
                    <span class="font-medium text-slate-600">{{ __('Host organization') }}:</span>
                    {{ $event->organization->nameForLocale() }}
                </p>
            @endif

            <dl class="mt-8 space-y-4 text-sm">
                <div>
                    <dt class="font-semibold text-slate-800">{{ __('Event time') }}</dt>
                    <dd class="mt-1 text-slate-600">
                        {{ $event->event_starts_at->locale(app()->getLocale())->isoFormat('LLLL') }}
                        <span class="text-slate-400">—</span>
                        {{ $event->event_ends_at->locale(app()->getLocale())->isoFormat('LLLL') }}
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-800">{{ __('Roster') }}</dt>
                    <dd class="mt-1 text-slate-600">
                        @if ($event->capacity === null)
                            {{ __('Open roster (no fixed limit)') }}
                        @else
                            {{ __(':current of :capacity volunteers registered', ['current' => $event->volunteers_count, 'capacity' => $event->capacity]) }}
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-8 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-medium text-slate-800">{{ __('Volunteer with SwaedUAE') }}</p>
                <p class="mt-2">{{ __('Public event volunteer CTA') }}</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-100 pt-8">
                <a href="{{ route('volunteer.opportunities.show', $event) }}" class="btn-primary-solid">{{ __('View opportunity') }}</a>
                @guest
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">{{ __('Create account') }}</a>
                @endguest
            </div>
        </article>
    </div>
</x-public-layout>
