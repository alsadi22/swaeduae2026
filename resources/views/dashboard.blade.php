<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-slate-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            @if (auth()->user()->hasRole('volunteer'))
                @if (! $volunteerProfileCompleteForCommitments)
                    <div class="overflow-hidden border border-amber-200 bg-amber-50/90 shadow-sm sm:rounded-lg">
                        <div class="border-b border-amber-100 px-6 py-4">
                            <h3 class="font-display text-lg font-bold text-amber-950">{{ __('Volunteer profile for commitments') }}</h3>
                            <p class="mt-1 text-sm text-amber-900/90">{{ __('Dashboard volunteer profile hint') }}</p>
                        </div>
                        <div class="p-6">
                            <a href="{{ route('volunteer.profile.edit') }}" class="inline-flex text-sm font-bold text-emerald-900 hover:underline">{{ __('Complete volunteer profile') }} →</a>
                        </div>
                    </div>
                @endif

                <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Verified volunteer time') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Dashboard hours summary hint') }}</p>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap items-end gap-8">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Total verified minutes') }}</p>
                                <p class="mt-1 font-display text-3xl font-bold text-emerald-900">{{ number_format($verifiedVolunteerMinutesTotal) }}</p>
                                <span data-testid="dashboard-verified-minutes-total" class="sr-only">{{ $verifiedVolunteerMinutesTotal }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Verified hours (rounded)') }}</p>
                                <p class="mt-1 font-display text-3xl font-bold text-emerald-900">{{ number_format($verifiedVolunteerHoursRounded) }}</p>
                                <span data-testid="dashboard-verified-hours-rounded" class="sr-only">{{ $verifiedVolunteerHoursRounded }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Completed sessions') }}</p>
                                <p class="mt-1 font-display text-2xl font-bold text-slate-800">{{ number_format($verifiedVolunteerSessionsCount) }}</p>
                                <span data-testid="dashboard-verified-sessions-count" class="sr-only">{{ $verifiedVolunteerSessionsCount }}</span>
                            </div>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-4 border-t border-slate-100 pt-6">
                            <a href="{{ route('dashboard.attendance.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('My attendance') }} →</a>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden border border-dashed border-slate-200 bg-slate-50/80 shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Certificates') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Certificates dashboard placeholder') }}</p>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600">{{ __('Certificates coming soon message') }}</p>
                    </div>
                </div>

                <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Stay informed') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Stay informed dashboard intro') }}</p>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('volunteer.profile.edit') }}" class="inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Manage email preferences') }} →</a>
                        <p class="text-xs text-slate-500">{{ __('Stay informed in-app note') }}</p>
                    </div>
                </div>

                <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Your opportunity applications') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Applications you submitted for events that require approval.') }}</p>
                    </div>
                    <form method="get" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-6 py-4">
                        @foreach (['past_page', 'upcoming_page'] as $dashPageKey)
                            @if (filled(request($dashPageKey)))
                                <input type="hidden" name="{{ $dashPageKey }}" value="{{ request($dashPageKey) }}" />
                            @endif
                        @endforeach
                        <div>
                            <label for="dash_app_status" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Filter applications') }}</label>
                            <select id="dash_app_status" name="application_status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-52">
                                <option value="all" @selected(($applicationStatusFilter ?? 'all') === 'all')>{{ __('All applications') }}</option>
                                <option value="{{ \App\Models\EventApplication::STATUS_PENDING }}" @selected(($applicationStatusFilter ?? '') === \App\Models\EventApplication::STATUS_PENDING)>{{ __('Application status pending') }}</option>
                                <option value="{{ \App\Models\EventApplication::STATUS_APPROVED }}" @selected(($applicationStatusFilter ?? '') === \App\Models\EventApplication::STATUS_APPROVED)>{{ __('Application status approved') }}</option>
                                <option value="{{ \App\Models\EventApplication::STATUS_REJECTED }}" @selected(($applicationStatusFilter ?? '') === \App\Models\EventApplication::STATUS_REJECTED)>{{ __('Application status rejected') }}</option>
                                <option value="{{ \App\Models\EventApplication::STATUS_WITHDRAWN }}" @selected(($applicationStatusFilter ?? '') === \App\Models\EventApplication::STATUS_WITHDRAWN)>{{ __('Application status withdrawn') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-sm hover:bg-emerald-800">{{ __('Apply filters') }}</button>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear filters') }}</a>
                        </div>
                    </form>
                    <div class="p-6">
                        @if ($myApplications->isEmpty())
                            <p class="text-sm text-slate-600">
                                @if (($applicationStatusFilter ?? 'all') !== 'all')
                                    {{ __('No applications match your filters.') }}
                                @else
                                    {{ __('No applications yet.') }}
                                @endif
                            </p>
                            <a href="{{ route('volunteer.opportunities.index') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Browse opportunities') }} →</a>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach ($myApplications as $app)
                                    <li class="py-4 first:pt-0">
                                        @if ($app->event)
                                            <a href="{{ route('volunteer.opportunities.show', $app->event) }}" class="font-semibold text-slate-900 hover:text-emerald-800 hover:underline">{{ $app->event->titleForLocale() }}</a>
                                            @if ($app->event->organization)
                                                <p class="text-xs text-slate-500">{{ $app->event->organization->nameForLocale() }}</p>
                                            @endif
                                        @else
                                            <span class="font-semibold text-slate-500">{{ __('Event no longer available') }}</span>
                                        @endif
                                        <p class="mt-2 text-sm">
                                            <span class="font-semibold text-slate-700">{{ __('Status') }}:</span>
                                            @switch($app->status)
                                                @case(\App\Models\EventApplication::STATUS_PENDING)
                                                    <span class="text-amber-800">{{ __('Application status pending') }}</span>
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_APPROVED)
                                                    <span class="text-emerald-800">{{ __('Application status approved') }}</span>
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_REJECTED)
                                                    <span class="text-red-800">{{ __('Application status rejected') }}</span>
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_WITHDRAWN)
                                                    <span class="text-slate-600">{{ __('Application status withdrawn') }}</span>
                                                    @break
                                                @default
                                                    <span class="text-slate-600">{{ $app->status }}</span>
                                            @endswitch
                                        </p>
                                        @if ($app->status === \App\Models\EventApplication::STATUS_REJECTED && filled($app->review_note))
                                            <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $app->review_note }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-slate-500">{{ __('Updated') }} {{ $app->updated_at->diffForHumans() }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($myApplications->hasPages())
                                <div class="mt-6">
                                    {{ $myApplications->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Your upcoming volunteer commitments') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Events you have joined from the opportunities list.') }}</p>
                    </div>
                    <div class="p-6">
                        @if ($upcomingRosterEvents->isEmpty())
                            <p class="text-sm text-slate-600">{{ __('No upcoming rostered events.') }}</p>
                            <a href="{{ route('volunteer.opportunities.index') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Browse opportunities') }} →</a>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach ($upcomingRosterEvents as $ev)
                                    <li class="py-4 first:pt-0">
                                        <a href="{{ route('volunteer.opportunities.show', $ev) }}" class="font-semibold text-slate-900 hover:text-emerald-800 hover:underline">{{ $ev->titleForLocale() }}</a>
                                        @if ($ev->organization)
                                            <p class="text-xs text-slate-500">{{ $ev->organization->nameForLocale() }}</p>
                                        @endif
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ $ev->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                            — {{ $ev->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                        </p>
                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                                            <a href="{{ route('volunteer.opportunities.attendance', $ev) }}" class="inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Open attendance check-in') }} →</a>
                                            @can('leaveRoster', $ev)
                                                <form action="{{ route('volunteer.opportunities.leave', $ev) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Leave this opportunity? You can join again before the event starts if slots allow.')));">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800">{{ __('Leave roster') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($upcomingRosterEvents->hasPages())
                                <div class="mt-6">
                                    {{ $upcomingRosterEvents->links() }}
                                </div>
                            @endif
                            <a href="{{ route('volunteer.opportunities.index') }}" class="mt-4 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Browse more opportunities') }} →</a>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Past volunteer commitments') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Events you were rostered on that have ended.') }}</p>
                    </div>
                    <div class="p-6">
                        @if ($pastRosterEvents->isEmpty())
                            <p class="text-sm text-slate-600">{{ __('No past rostered events yet.') }}</p>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach ($pastRosterEvents as $ev)
                                    <li class="py-4 first:pt-0">
                                        <span class="font-semibold text-slate-900">{{ $ev->titleForLocale() }}</span>
                                        @if ($ev->organization)
                                            <p class="text-xs text-slate-500">{{ $ev->organization->nameForLocale() }}</p>
                                        @endif
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ $ev->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                            — {{ $ev->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($pastRosterEvents->hasPages())
                                <div class="mt-6">
                                    {{ $pastRosterEvents->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
