<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Check-in log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Filter check-in attempts') }}</h3>
                </div>
                <form method="get" action="{{ route('admin.checkin-attempts.index') }}" class="flex flex-wrap items-end gap-4 p-6">
                    <div>
                        <x-input-label for="filter_outcome" :value="__('Outcome')" />
                        <select id="filter_outcome" name="outcome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                            <option value="all" @selected($outcomeFilter === 'all')>{{ __('All outcomes') }}</option>
                            <option value="accepted" @selected($outcomeFilter === 'accepted')>{{ __('Outcome accepted') }}</option>
                            <option value="suspicious" @selected($outcomeFilter === 'suspicious')>{{ __('Outcome suspicious') }}</option>
                            <option value="rejected" @selected($outcomeFilter === 'rejected')>{{ __('Outcome rejected') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_event_id" :value="__('Event')" />
                        <select id="filter_event_id" name="event_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:min-w-[14rem]">
                            <option value="">{{ __('All events') }}</option>
                            @foreach ($filterEvents as $fe)
                                <option value="{{ $fe->id }}" @selected((string) ($eventId ?? '') === (string) $fe->id)>{{ $fe->title_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[12rem] flex-1 sm:min-w-[16rem]">
                        <x-input-label for="filter_checkin_search" :value="__('Volunteer name or email')" />
                        <input type="search" id="filter_checkin_search" name="search" value="{{ $search }}" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                        <a href="{{ route('admin.checkin-attempts.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($attempts->isEmpty())
                        <p class="text-sm text-gray-600">
                            @if ($outcomeFilter !== 'all' || $eventId || filled($search))
                                {{ __('No check-in attempts match your filters.') }}
                            @else
                                {{ __('No attendance records yet.') }}
                            @endif
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('When') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Event') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Volunteer') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Type') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Outcome') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Distance meters') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Rejection reason') }}</th>
                                        <th class="px-2 py-2 text-start font-semibold text-gray-700">{{ __('Suspicion flags') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($attempts as $a)
                                        <tr>
                                            <td class="whitespace-nowrap px-2 py-2 text-gray-600">{{ $a->created_at?->format('Y-m-d H:i') }}</td>
                                            <td class="px-2 py-2 text-gray-900">{{ $a->event?->title_en ?? '—' }}</td>
                                            <td class="px-2 py-2 text-gray-700">{{ $a->user?->name ?? '—' }}</td>
                                            <td class="px-2 py-2 text-gray-700">
                                                @if ($a->attempt_type === \App\Models\CheckinAttempt::TYPE_CHECK_IN)
                                                    {{ __('Attempt type check_in') }}
                                                @elseif ($a->attempt_type === \App\Models\CheckinAttempt::TYPE_CHECK_OUT)
                                                    {{ __('Attempt type check_out') }}
                                                @else
                                                    {{ $a->attempt_type }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-2">
                                                @if ($a->outcome === 'accepted')
                                                    <span class="text-emerald-800">{{ __('Outcome accepted') }}</span>
                                                @elseif ($a->outcome === 'suspicious')
                                                    <span class="text-amber-800">{{ __('Outcome suspicious') }}</span>
                                                @elseif ($a->outcome === 'rejected')
                                                    <span class="text-red-800">{{ __('Outcome rejected') }}</span>
                                                @else
                                                    {{ $a->outcome }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-gray-600">{{ $a->distance_meters !== null ? number_format($a->distance_meters, 1) : '—' }}</td>
                                            <td class="max-w-[12rem] truncate px-2 py-2 text-gray-600" title="{{ $a->rejection_reason }}">{{ $a->rejection_reason ?? '—' }}</td>
                                            <td class="max-w-[10rem] truncate px-2 py-2 text-gray-600" title="{{ is_array($a->flags) ? implode(', ', $a->flags) : '' }}">{{ ! empty($a->flags) ? implode(', ', $a->flags) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
