<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('External news') }}</h2>
            <a href="{{ route('admin.external-news-sources.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Manage sources') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif

            <form method="get" action="{{ route('admin.external-news-items.index') }}" class="flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <x-input-label for="en_filter_status" :value="__('Status')" />
                    <select id="en_filter_status" name="status" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending_review" {{ ($filters['status'] ?? '') === 'pending_review' ? 'selected' : '' }}>{{ __('Pending review') }}</option>
                        <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                        <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="en_filter_source" :value="__('Source')" />
                    <select id="en_filter_source" name="source_id" class="mt-1 block w-56 rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All sources') }}</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src->id }}" {{ (string) ($filters['source_id'] ?? '') === (string) $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
            </form>

            <form method="post" action="{{ route('admin.external-news-items.bulk') }}" class="bg-white p-4 shadow-sm sm:rounded-lg border border-gray-100">
                @csrf
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">{{ __('Bulk action') }}</span>
                    <select name="bulk_action" class="rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="approve">{{ __('Approve pending') }}</option>
                        <option value="publish">{{ __('Publish pending / approved') }}</option>
                        <option value="reject">{{ __('Reject') }}</option>
                    </select>
                    <x-primary-button type="submit">{{ __('Apply to selected') }}</x-primary-button>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-2 w-10"></th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Title') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Source') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Status') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Fetched') }}</th>
                                <th class="pb-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="py-3 pe-2">
                                        <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" class="rounded border-gray-300">
                                    </td>
                                    <td class="py-3 pe-4 font-medium text-gray-900 max-w-xs truncate">{{ $item->original_title }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $item->source->name }}</td>
                                    <td class="py-3 pe-4"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs">{{ $item->status }}</span></td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $item->fetched_at?->diffForHumans() }}</td>
                                    <td class="py-3 text-end space-x-2">
                                        <a href="{{ route('admin.external-news-items.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        @if ($item->external_url)
                                            <a href="{{ $item->external_url }}" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-gray-900">{{ __('Original') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-500">{{ __('No imported items match.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="px-1">{{ $items->links() }}</div>
        </div>
    </div>
</x-app-layout>
