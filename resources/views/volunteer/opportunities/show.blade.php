@php
    $pageTitle = $event->titleForLocale().' — '.__('Volunteer opportunities');
    $metaDescription = \Illuminate\Support\Str::limit(
        $event->titleForLocale().' — '.__('Volunteer attendance hint'),
        220
    );
    $ogImage = \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    $localeQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Volunteer'), 'url' => route('volunteer.index', $localeQ, true)],
        ['name' => __('Volunteer opportunities'), 'url' => route('volunteer.opportunities.index', $localeQ, true)],
        ['name' => \Illuminate\Support\Str::limit($event->titleForLocale(), 72), 'url' => route('volunteer.opportunities.show', array_merge(['event' => $event], $localeQ), true)],
    ];
    $extraJsonLd = \App\Support\SwaedUaeStructuredData::publicEventForJsonLd($event);
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="route('volunteer.opportunities.show', array_merge(['event' => $event], $localeQ), true)"
    :ogTitle="$event->titleForLocale()"
    :ogDescription="$metaDescription"
    ogType="article"
    :ogImage="$ogImage"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$extraJsonLd"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline">← {{ __('Volunteer opportunities') }}</a>

        <article class="card-surface mt-8 max-w-3xl p-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
            @endif

            @auth
                @if (auth()->user()->hasRole('volunteer') && ($pendingApplicationsOnOtherEventsCount ?? 0) > 0)
                    <div data-testid="pending-applications-other-events-notice" class="mb-6 rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950" role="status">
                        {{ trans_choice('site.volunteer_opportunity_other_pending_applications', $pendingApplicationsOnOtherEventsCount) }}
                    </div>
                @endif
            @endauth

            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-display text-2xl font-bold text-emerald-950 sm:text-3xl">{{ $event->titleForLocale() }}</h1>
                        @if ($event->application_required)
                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900">{{ __('Requires application') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-shrink-0 flex-wrap items-center gap-2" x-data="{ copied: false }">
                    <button
                        type="button"
                        data-testid="opportunity-copy-link"
                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:border-emerald-200 hover:text-emerald-900"
                        @click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(url()->current()) }}).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
                    >
                        <span x-show="!copied">{{ __('Copy page link') }}</span>
                        <span x-show="copied" x-cloak>{{ __('Link copied') }}</span>
                    </button>
                    @auth
                        @if (auth()->user()->hasRole('volunteer'))
                            @can('saveOpportunity', $event)
                                @if ($opportunitySavedByViewer ?? false)
                                    <form action="{{ route('volunteer.opportunities.unsave', array_merge(['event' => $event], $localeQ)) }}" method="post" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-testid="opportunity-unsave" class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-amber-200 hover:text-amber-950">{{ __('Remove from saved') }}</button>
                                    </form>
                                @else
                                    <form action="{{ route('volunteer.opportunities.save', array_merge(['event' => $event], $localeQ)) }}" method="post" class="inline">
                                        @csrf
                                        <button type="submit" data-testid="opportunity-save" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100">{{ __('Save for later') }}</button>
                                    </form>
                                @endif
                            @endcan
                        @endif
                    @endauth
                </div>
            </div>
            @if ($event->organization)
                <p class="mt-2 text-sm text-slate-500">
                    <span class="font-medium text-slate-600">{{ __('Host organization') }}:</span>
                    {{ $event->organization->nameForLocale() }}
                </p>
            @endif

            <p class="mt-3 text-sm">
                <a href="{{ route('events.show', array_merge(['event' => $event], $localeQ)) }}" class="font-semibold text-emerald-800 hover:underline" data-testid="opportunity-public-calendar-link">{{ __('View on public calendar') }}</a>
            </p>

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
                    <dt class="font-semibold text-slate-800">{{ __('Check-in window') }}</dt>
                    <dd class="mt-1 text-slate-600">
                        {{ $event->checkin_window_starts_at->locale(app()->getLocale())->isoFormat('LLLL') }}
                        <span class="text-slate-400">—</span>
                        {{ $event->checkin_window_ends_at->locale(app()->getLocale())->isoFormat('LLLL') }}
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

            <div class="card-surface--muted mt-8 p-4 text-sm text-slate-600">
                <p class="font-medium text-slate-800">{{ __('Location and check-in') }}</p>
                <p class="mt-2">{{ __('Exact coordinates are used only for GPS check-in validation for volunteers on the roster.') }}</p>
            </div>

            <div class="mt-8 border-t border-slate-100 pt-8">
                <p class="text-sm font-medium text-slate-800">{{ __('Join this opportunity') }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ __('Volunteer attendance hint') }}</p>

                @auth
                    @if (auth()->user()->hasRole('volunteer'))
                        @if (! $volunteerProfileCompleteForCommitments)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950" role="status">
                                <p class="font-semibold">{{ __('Opportunity profile required hint') }}</p>
                                <a href="{{ route('volunteer.profile.edit', $localeQ) }}" class="mt-2 inline-flex font-bold text-emerald-900 hover:underline">{{ __('Complete volunteer profile') }} →</a>
                            </div>
                        @endif
                        @if ($event->userIsOnRoster(auth()->user()))
                            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ __('You are on the roster for this opportunity.') }}</p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ route('volunteer.opportunities.attendance', array_merge(['event' => $event], $localeQ)) }}" class="btn-primary-solid">{{ __('Open attendance check-in') }}</a>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">{{ __('Opens a secure link valid for 7 days—same technology as QR codes from your coordinator.') }}</p>
                            @can('leaveRoster', $event)
                                <form action="{{ route('volunteer.opportunities.leave', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-6" onsubmit="return confirm(@json(__('Leave this opportunity? You can join again before the event starts if slots allow.')));">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">{{ __('Leave roster') }}</button>
                                </form>
                            @endcan
                        @else
                        @if ($event->application_required)
                            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                                <p class="text-sm font-semibold text-slate-800">{{ __('Application required') }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ __('This opportunity requires admin approval before you can join the roster.') }}</p>

                                @if ($application?->status === \App\Models\EventApplication::STATUS_PENDING)
                                    <p class="mt-3 text-sm font-medium text-amber-900">{{ __('Your application is pending review.') }}</p>
                                    @can('withdrawApplication', $event)
                                        <form action="{{ route('volunteer.opportunities.withdraw-application', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-3" onsubmit="return confirm(@json(__('Withdraw your application?')));">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('Withdraw application') }}</button>
                                        </form>
                                    @endcan
                                @elseif ($application?->status === \App\Models\EventApplication::STATUS_APPROVED)
                                    <p class="mt-3 text-sm font-medium text-emerald-900">{{ __('Your application was approved. You can join the roster below.') }}</p>
                                @elseif ($application?->status === \App\Models\EventApplication::STATUS_REJECTED)
                                    <p class="mt-3 text-sm font-medium text-red-800">{{ __('Your application was not approved.') }}</p>
                                    @if (filled($application->review_note))
                                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 whitespace-pre-wrap">{{ $application->review_note }}</div>
                                    @endif
                                    @can('applyToEvent', $event)
                                        <form action="{{ route('volunteer.opportunities.apply', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-4 space-y-3">
                                            @csrf
                                            <div>
                                                <label for="apply_message" class="block text-xs font-medium text-slate-700">{{ __('Optional message to organizers') }}</label>
                                                <textarea id="apply_message" name="message" rows="3" maxlength="2000" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                                            </div>
                                            <button type="submit" class="btn-primary-solid">{{ __('Apply again') }}</button>
                                        </form>
                                    @endcan
                                @elseif ($application?->status === \App\Models\EventApplication::STATUS_WITHDRAWN)
                                    <p class="mt-3 text-sm text-slate-600">{{ __('You withdrew your application.') }}</p>
                                    @can('applyToEvent', $event)
                                        <form action="{{ route('volunteer.opportunities.apply', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-4 space-y-3">
                                            @csrf
                                            <div>
                                                <label for="apply_message_w" class="block text-xs font-medium text-slate-700">{{ __('Optional message to organizers') }}</label>
                                                <textarea id="apply_message_w" name="message" rows="3" maxlength="2000" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                                            </div>
                                            <button type="submit" class="btn-primary-solid">{{ __('Submit application') }}</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('applyToEvent', $event)
                                        <form action="{{ route('volunteer.opportunities.apply', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-4 space-y-3">
                                            @csrf
                                            <div>
                                                <label for="apply_message_new" class="block text-xs font-medium text-slate-700">{{ __('Optional message to organizers') }}</label>
                                                <textarea id="apply_message_new" name="message" rows="3" maxlength="2000" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                                            </div>
                                            <button type="submit" class="btn-primary-solid">{{ __('Submit application') }}</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        @endif

                            @can('joinRoster', $event)
                                <form action="{{ route('volunteer.opportunities.join', array_merge(['event' => $event], $localeQ)) }}" method="post" class="mt-4">
                                    @csrf
                                    <button type="submit" class="btn-primary-solid">{{ __('Join roster') }}</button>
                                </form>
                            @else
                                @if ($event->event_ends_at < now())
                                    <p class="mt-4 text-sm text-amber-800">{{ __('This opportunity is no longer open for joining.') }}</p>
                                @elseif ($event->capacity !== null && $event->volunteers_count >= $event->capacity)
                                    <p class="mt-4 text-sm text-amber-800">{{ __('This roster is full.') }}</p>
                                @elseif ($event->application_required)
                                    @if (! $application || ! $application->isApproved())
                                        <p class="mt-4 text-sm text-amber-800">{{ __('You need an approved application before you can join this roster.') }}</p>
                                    @endif
                                @endif
                            @endcan
                        @endif
                    @else
                        <p class="mt-4 text-sm text-slate-600">{{ __('Volunteer role required to join opportunities. Contact support if you need help.') }}</p>
                        <a href="{{ route('dashboard', $localeQ) }}" class="mt-3 inline-flex text-sm font-semibold text-emerald-800 hover:underline">{{ __('Dashboard') }}</a>
                    @endif
                @else
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('login', array_merge(\App\Support\IntendedUrl::queryParamsForRequestUri(request()), $localeQ)) }}" class="btn-primary-solid">{{ __('Log in') }}</a>
                        @if (Route::has('register.volunteer'))
                            <a href="{{ route('register.volunteer', array_merge(\App\Support\IntendedUrl::queryParamsForRequestUri(request()), $localeQ)) }}" class="btn-secondary-muted">{{ __('Create volunteer account') }}</a>
                        @elseif (Route::has('register'))
                            <a href="{{ route('register', $localeQ) }}" class="btn-secondary-muted">{{ __('Register') }}</a>
                        @endif
                    </div>
                @endauth
            </div>
        </article>
    </div>
</x-public-layout>
