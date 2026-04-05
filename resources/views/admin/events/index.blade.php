<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin events') }}
            </h2>
            <a href="{{ route('admin.events.create', $adminLocaleQ) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                {{ __('New event') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="get" action="{{ route('admin.events.index', $adminLocaleQ) }}" class="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <x-input-label for="admin_events_search" :value="__('Search events')" />
                    <x-text-input id="admin_events_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" maxlength="100" autocomplete="off" placeholder="{{ __('Title or organization name') }}" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                    @if (filled($search))
                        <a href="{{ route('admin.events.index', $adminLocaleQ) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Title') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Organization') }}</th>
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
                                    <td class="py-3 pe-4 text-gray-700">{{ $e->organization?->name_en ?? '—' }}</td>
                                    <td class="py-3 pe-4 text-gray-600 whitespace-nowrap">{{ $e->event_starts_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $e->capacity ?? '—' }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $e->volunteers_count }}</td>
                                    <td class="py-3 text-end space-x-2">
                                        <a href="{{ route('admin.events.edit', array_merge(['event' => $e], $adminLocaleQ)) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        <form action="{{ route('admin.events.destroy', array_merge(['event' => $e], $adminLocaleQ)) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this event?')));">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-500">
                                        @if (filled($search))
                                            {{ __('No events match your filters.') }}
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
