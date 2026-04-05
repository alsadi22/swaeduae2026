<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('News sources') }}</h2>
            <a href="{{ route('admin.external-news-sources.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                {{ __('Add source') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Name') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Type') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Active') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Last fetch') }}</th>
                                <th class="pb-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($sources as $s)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-gray-900">{{ $s->name }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $s->type }}</td>
                                    <td class="py-3 pe-4">{{ $s->is_active ? __('Yes') : __('No') }}</td>
                                    <td class="py-3 pe-4 text-gray-600">
                                        @if ($s->latestFetchLog?->finished_at)
                                            {{ $s->latestFetchLog->finished_at->diffForHumans() }}
                                            <span class="text-xs text-gray-400">({{ $s->latestFetchLog->status }})</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 text-end space-x-2">
                                        <a href="{{ route('admin.external-news-sources.logs', $s) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Logs') }}</a>
                                        <a href="{{ route('admin.external-news-sources.edit', $s) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        <form action="{{ route('admin.external-news-sources.fetch', $s) }}" method="post" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">{{ __('Fetch now') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-500">{{ __('No sources yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
