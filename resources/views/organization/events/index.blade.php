<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organization portal events title') }}
            </h2>
            @can('configure-organization-events')
                <a href="{{ route('organization.events.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                    {{ __('New event') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <p class="mb-6 text-sm text-gray-600">{{ __('Organization portal events hint') }}</p>

            <form method="get" action="{{ route('organization.events.index') }}" class="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <x-input-label for="org_events_search" :value="__('Search events')" />
                    <x-text-input id="org_events_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" autocomplete="off" />
                </div>
                <div>
                    <x-input-label for="org_events_timing" :value="__('Show events')" />
                    <select id="org_events_timing" name="timing" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-44">
                        <option value="all" @selected($timing === 'all')>{{ __('All events') }}</option>
                        <option value="upcoming" @selected($timing === 'upcoming')>{{ __('Upcoming events') }}</option>
                        <option value="past" @selected($timing === 'past')>{{ __('Past events') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="org_events_sort" :value="__('Sort events by start')" />
                    <select id="org_events_sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                        <option value="starts_desc" @selected($sort === 'starts_desc')>{{ __('Latest start first') }}</option>
                        <option value="starts_asc" @selected($sort === 'starts_asc')>{{ __('Earliest start first') }}</option>
                    </select>
                </div>
                <x-secondary-button type="submit">{{ __('Apply filters') }}</x-secondary-button>
                @if (filled($search) || $timing !== 'all' || $sort !== 'starts_desc')
                    <a href="{{ route('organization.events.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Clear filters') }}</a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Title') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Event starts') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Capacity') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Roster') }}</th>
                                <th class="pb-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($events as $e)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-gray-900">{{ $e->title_en }}</td>
                                    <td class="py-3 pe-4 text-gray-600 whitespace-nowrap">{{ $e->event_starts_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $e->capacity ?? '—' }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $e->volunteers_count }}</td>
                                    <td class="py-3 text-end space-x-2">
                                        @can('viewInOrganizationPortal', $e)
                                            <a href="{{ route('organization.events.roster', $e) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Roster') }}</a>
                                        @endcan
                                        @can('configureInOrganizationPortal', $e)
                                            <a href="{{ route('organization.events.edit', $e) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('deleteInOrganizationPortal', $e)
                                            <form action="{{ route('organization.events.destroy', $e) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this event?')));">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        @if (filled($search) || $timing !== 'all')
                                            {{ __('No organization events match filters.') }}
                                        @else
                                            {{ __('No events yet.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
