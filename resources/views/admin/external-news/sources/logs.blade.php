@php
    $appShellTitle = __('Fetch logs').' — '.\Illuminate\Support\Str::limit($source->name, 60, '…').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Fetch logs') }} — {{ $source->name }}</h2>
            <a href="{{ route('admin.external-news-sources.index', $adminLocaleQ) }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Back to sources') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
                        <x-copy-filtered-list-url-button class="[&_button]:border-gray-300 [&_button]:text-gray-700" test-id="admin-external-news-source-logs-copy-filtered-url" />
                    </div>
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Started') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Status') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Found') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('New') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Updated') }}</th>
                                <th class="pb-3 font-medium">{{ __('Error') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="py-3 pe-4 text-gray-700">{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-3 pe-4">{{ $log->status }}</td>
                                    <td class="py-3 pe-4">{{ $log->items_found }}</td>
                                    <td class="py-3 pe-4">{{ $log->items_created }}</td>
                                    <td class="py-3 pe-4">{{ $log->items_updated }}</td>
                                    <td class="py-3 text-gray-600 max-w-md truncate" title="{{ $log->error_message }}">{{ $log->error_message ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-500">{{ __('No logs yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $logs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
