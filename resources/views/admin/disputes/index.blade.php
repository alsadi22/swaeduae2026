<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Disputes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Filter disputes') }}</h3>
                </div>
                <form method="get" action="{{ route('admin.disputes.index', $adminLocaleQ) }}" class="flex flex-wrap items-end gap-4 p-6">
                    <div>
                        <x-input-label for="filter_status" :value="__('Status')" />
                        <select id="filter_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                            <option value="all" @selected($statusFilter === 'all')>{{ __('All statuses') }}</option>
                            <option value="{{ \App\Models\Dispute::STATUS_OPEN }}" @selected($statusFilter === \App\Models\Dispute::STATUS_OPEN)>{{ __('Dispute status open') }}</option>
                            <option value="{{ \App\Models\Dispute::STATUS_RESOLVED }}" @selected($statusFilter === \App\Models\Dispute::STATUS_RESOLVED)>{{ __('Dispute status resolved') }}</option>
                            <option value="{{ \App\Models\Dispute::STATUS_DISMISSED }}" @selected($statusFilter === \App\Models\Dispute::STATUS_DISMISSED)>{{ __('Dispute status dismissed') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_dispute_event_id" :value="__('Event')" />
                        <select id="filter_dispute_event_id" name="event_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:min-w-[14rem]">
                            <option value="">{{ __('All events') }}</option>
                            @foreach ($filterEvents as $fe)
                                <option value="{{ $fe->id }}" @selected((string) ($eventId ?? '') === (string) $fe->id)>{{ $fe->title_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[12rem] flex-1 sm:min-w-[16rem]">
                        <x-input-label for="filter_dispute_search" :value="__('Volunteer name or email')" />
                        <input type="search" id="filter_dispute_search" name="search" value="{{ $search }}" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                        <a href="{{ route('admin.disputes.index', $adminLocaleQ) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($disputes->isEmpty())
                        <p class="text-sm text-gray-600">
                            @if ($statusFilter !== 'all' || $eventId || filled($search))
                                {{ __('No disputes match your filters.') }}
                            @else
                                {{ __('No disputes yet.') }}
                            @endif
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-start font-semibold text-gray-700">{{ __('Event') }}</th>
                                        <th class="px-3 py-2 text-start font-semibold text-gray-700">{{ __('Volunteer') }}</th>
                                        <th class="px-3 py-2 text-start font-semibold text-gray-700">{{ __('Status') }}</th>
                                        <th class="px-3 py-2 text-start font-semibold text-gray-700">{{ __('Updated') }}</th>
                                        <th class="px-3 py-2 text-start font-semibold text-gray-700"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($disputes as $d)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900">{{ $d->attendance?->event?->title_en ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $d->attendance?->user?->name ?? '—' }}</td>
                                            <td class="px-3 py-2">
                                                @switch($d->status)
                                                    @case(\App\Models\Dispute::STATUS_OPEN)
                                                        <span class="text-amber-800">{{ __('Dispute status open') }}</span>
                                                        @break
                                                    @case(\App\Models\Dispute::STATUS_RESOLVED)
                                                        <span class="text-emerald-800">{{ __('Dispute status resolved') }}</span>
                                                        @break
                                                    @case(\App\Models\Dispute::STATUS_DISMISSED)
                                                        <span class="text-slate-600">{{ __('Dispute status dismissed') }}</span>
                                                        @break
                                                    @default
                                                        {{ $d->status }}
                                                @endswitch
                                            </td>
                                            <td class="px-3 py-2 text-gray-600">{{ $d->updated_at->diffForHumans() }}</td>
                                            <td class="px-3 py-2 text-end">
                                                <a href="{{ route('admin.disputes.show', array_merge(['dispute' => $d], $adminLocaleQ)) }}" class="font-semibold text-indigo-700 hover:text-indigo-900">{{ __('Details') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            {{ $disputes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
