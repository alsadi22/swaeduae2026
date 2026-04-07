@php
    $appShellTitle = __('Flagged attendance').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Flagged attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite" data-testid="admin-flagged-attendance-flash-status">{{ session('status') }}</div>
            @endif
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Filter flagged attendance') }}</h3>
                </div>
                <form method="get" action="{{ route('admin.flagged-attendance.index', $adminLocaleQ) }}" class="flex flex-wrap items-end gap-4 p-6">
                    <div>
                        <x-input-label for="flagged_volunteer_search" :value="__('Search volunteer')" />
                        <x-text-input id="flagged_volunteer_search" name="search" type="search" class="mt-1 block w-56 max-w-full" :value="$search" maxlength="100" autocomplete="off" placeholder="{{ __('Volunteer name or email') }}" />
                    </div>
                    <div>
                        <x-input-label for="filter_event_id" :value="__('Event')" />
                        <select id="filter_event_id" name="event_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:min-w-[14rem]">
                            <option value="">{{ __('All events') }}</option>
                            @foreach ($filterEvents as $fe)
                                <option value="{{ $fe->id }}" @selected((string) $eventId === (string) $fe->id)>{{ $fe->title_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                        @if (filled($search) || $eventId !== null)
                            <a href="{{ route('admin.flagged-attendance.index', $adminLocaleQ) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                        @endif
                        @php
                            $flaggedExportQs = array_filter(
                                array_merge($adminLocaleQ, request()->only(['event_id', 'search'])),
                                static fn ($v) => $v !== null && $v !== ''
                            );
                        @endphp
                        <a
                            href="{{ route('admin.flagged-attendance.export', $flaggedExportQs) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                            data-testid="admin-flagged-attendance-export-csv"
                        >{{ __('Download flagged attendance CSV') }}</a>
                        <x-copy-filtered-list-url-button class="[&_button]:border-gray-300 [&_button]:text-gray-700" test-id="admin-flagged-attendance-copy-filtered-url" />
                    </div>
                    <p class="mt-3 text-xs text-gray-500">{{ __('Admin flagged attendance export hint') }}</p>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($rows->total() === 0)
                        <p class="text-sm text-gray-600">
                            @if (filled($search) || $eventId !== null)
                                {{ __('No flagged attendance matches your filters.') }}
                            @else
                                {{ __('No flagged attendance records.') }}
                            @endif
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Updated') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Event') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Volunteer') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Status') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Verified minutes') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Suspicion flags on record') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Adjust time') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td class="whitespace-nowrap px-2 py-2 text-gray-600">{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                                            <td class="px-2 py-2 text-gray-900">{{ $row->event?->title_en ?? '—' }}</td>
                                            <td class="px-2 py-2 text-gray-700">{{ $row->user?->name ?? '—' }}</td>
                                            <td class="px-2 py-2 text-gray-700">
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
                                            </td>
                                            <td class="px-2 py-2 text-gray-600">
                                                @if ($row->verifiedMinutes() !== null)
                                                    {{ $row->verifiedMinutes() }}
                                                    @if ($row->minutes_adjustment !== null && $row->minutes_adjustment !== 0)
                                                        <span class="block text-[10px] text-gray-500">{{ __('Clock record') }} {{ $row->minutes_worked }} · {{ __('Adjustment') }} {{ $row->minutes_adjustment > 0 ? '+' : '' }}{{ $row->minutes_adjustment }}</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="max-w-[14rem] px-2 py-2 text-amber-900" title="{{ is_array($row->suspicion_flags) ? implode(', ', $row->suspicion_flags) : '' }}">
                                                {{ ! empty($row->suspicion_flags) ? implode(', ', $row->suspicion_flags) : '—' }}
                                            </td>
                                            <td class="min-w-[12rem] px-2 py-2 align-top text-gray-700">
                                                @can('adjustMinutes', $row)
                                                    <form method="post" action="{{ route('admin.attendances.minutes-adjustment.update', array_merge(['attendance' => $row], $adminLocaleQ)) }}" class="space-y-1">
                                                        @csrf
                                                        <label class="sr-only" for="adj_{{ $row->id }}">{{ __('Minutes adjustment') }}</label>
                                                        <input id="adj_{{ $row->id }}" type="number" name="minutes_adjustment" value="{{ old('minutes_adjustment', $row->minutes_adjustment) }}" min="-10080" max="10080" step="1" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Delta minutes') }}" />
                                                        <textarea name="minutes_adjustment_note" rows="2" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Note (optional)') }}">{{ old('minutes_adjustment_note', $row->minutes_adjustment_note) }}</textarea>
                                                        <button type="submit" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">{{ __('Save adjustment') }}</button>
                                                    </form>
                                                @else
                                                    —
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            {{ $rows->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
