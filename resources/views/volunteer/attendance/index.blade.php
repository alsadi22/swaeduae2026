@php
    $attLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $appShellTitle = __('My attendance').' — '.__('SwaedUAE');
@endphp
<x-app-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('My attendance') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite" data-testid="volunteer-attendance-flash-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('Filter attendance') }}</h3>
                </div>
                <form method="get" action="{{ route('dashboard.attendance.index', $attLocaleQ) }}" class="flex flex-wrap items-end gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <label for="filter_attendance_state" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Attendance state') }}</label>
                        <select id="filter_attendance_state" name="state" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-56">
                            <option value="all" @selected($stateFilter === 'all')>{{ __('All attendance states') }}</option>
                            <option value="{{ \App\Models\Attendance::STATE_PENDING }}" @selected($stateFilter === \App\Models\Attendance::STATE_PENDING)>{{ __('Attendance state pending') }}</option>
                            <option value="{{ \App\Models\Attendance::STATE_CHECKED_IN }}" @selected($stateFilter === \App\Models\Attendance::STATE_CHECKED_IN)>{{ __('Attendance state checked_in') }}</option>
                            <option value="{{ \App\Models\Attendance::STATE_CHECKED_OUT }}" @selected($stateFilter === \App\Models\Attendance::STATE_CHECKED_OUT)>{{ __('Attendance state checked_out') }}</option>
                            <option value="{{ \App\Models\Attendance::STATE_NO_SHOW }}" @selected($stateFilter === \App\Models\Attendance::STATE_NO_SHOW)>{{ __('Attendance state no_show') }}</option>
                            <option value="{{ \App\Models\Attendance::STATE_INCOMPLETE }}" @selected($stateFilter === \App\Models\Attendance::STATE_INCOMPLETE)>{{ __('Attendance state incomplete') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-sm hover:bg-emerald-800">{{ __('Apply filters') }}</button>
                        <a href="{{ route('dashboard.attendance.index', $attLocaleQ) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear filters') }}</a>
                        <x-copy-filtered-list-url-button test-id="attendance-copy-filtered-url" />
                    </div>
                </form>
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-sm text-slate-600">{{ __('Attendance history intro') }}</p>
                </div>
                <div class="p-6">
                    @if ($attendances->isEmpty())
                        <p class="text-sm text-slate-600">
                            @if ($stateFilter !== 'all')
                                {{ __('No attendance records match your filters.') }}
                            @else
                                {{ __('No attendance records yet.') }}
                            @endif
                        </p>
                        <a href="{{ route('volunteer.opportunities.index', $attLocaleQ) }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Browse opportunities') }} →</a>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($attendances as $row)
                                <li class="py-4 first:pt-0">
                                    @if ($row->event)
                                        <span class="font-semibold text-slate-900">{{ $row->event->titleForLocale() }}</span>
                                        @if ($row->event->organization)
                                            <p class="text-xs text-slate-500">{{ $row->event->organization->nameForLocale() }}</p>
                                        @endif
                                    @else
                                        <span class="font-semibold text-slate-500">{{ __('Event no longer available') }}</span>
                                    @endif
                                    <p class="mt-2 text-sm text-slate-700">
                                        <span class="font-semibold">{{ __('Status') }}:</span>
                                        @switch($row->state)
                                            @case(\App\Models\Attendance::STATE_PENDING)
                                                {{ __('Attendance state pending') }}
                                                @break
                                            @case(\App\Models\Attendance::STATE_CHECKED_IN)
                                                {{ __('Attendance state checked_in') }}
                                                @break
                                            @case(\App\Models\Attendance::STATE_CHECKED_OUT)
                                                {{ __('Attendance state checked_out') }}
                                                @break
                                            @case(\App\Models\Attendance::STATE_NO_SHOW)
                                                {{ __('Attendance state no_show') }}
                                                @break
                                            @case(\App\Models\Attendance::STATE_INCOMPLETE)
                                                {{ __('Attendance state incomplete') }}
                                                @break
                                            @default
                                                {{ $row->state }}
                                        @endswitch
                                    </p>
                                    @if ($row->state === \App\Models\Attendance::STATE_CHECKED_OUT && $row->minutes_worked !== null)
                                        <p class="mt-1 text-sm text-slate-600">
                                            <span class="font-semibold">{{ __('Verified minutes') }}:</span> {{ $row->verifiedMinutes() }}
                                        </p>
                                        @if ($row->minutes_adjustment !== null && $row->minutes_adjustment !== 0)
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ __('Clock record') }}: {{ $row->minutes_worked }} · {{ __('Adjustment') }}: {{ $row->minutes_adjustment > 0 ? '+' : '' }}{{ $row->minutes_adjustment }}
                                            </p>
                                        @endif
                                        @if (filled($row->minutes_adjustment_note))
                                            <p class="mt-1 text-xs text-slate-600">
                                                <span class="font-semibold">{{ __('Adjustment note') }}:</span>
                                                {{ $row->minutes_adjustment_note }}
                                            </p>
                                        @endif
                                    @endif
                                    @if (! empty($row->suspicion_flags))
                                        <p class="mt-1 text-xs text-amber-800">
                                            <span class="font-semibold">{{ __('Suspicion flags') }}:</span>
                                            {{ implode(', ', $row->suspicion_flags) }}
                                        </p>
                                    @endif
                                    @php
                                        $latestDispute = $row->disputes->first();
                                    @endphp
                                    @if ($latestDispute)
                                        <p class="mt-2 text-sm text-slate-600">
                                            <span class="font-semibold">{{ __('Disputes') }}:</span>
                                            @switch($latestDispute->status)
                                                @case(\App\Models\Dispute::STATUS_OPEN)
                                                    {{ __('Dispute status open') }}
                                                    @break
                                                @case(\App\Models\Dispute::STATUS_RESOLVED)
                                                    {{ __('Dispute status resolved') }}
                                                    @break
                                                @case(\App\Models\Dispute::STATUS_DISMISSED)
                                                    {{ __('Dispute status dismissed') }}
                                                    @break
                                                @default
                                                    {{ $latestDispute->status }}
                                            @endswitch
                                        </p>
                                    @endif
                                    @can('dispute', $row)
                                        <a href="{{ route('dashboard.attendance.disputes.create', array_merge(['attendance' => $row], $attLocaleQ)) }}" class="mt-2 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900 hover:underline">{{ __('Open dispute') }} →</a>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-6">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
