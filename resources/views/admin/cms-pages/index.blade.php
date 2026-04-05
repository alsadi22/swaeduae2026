<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('CMS pages') }}
            </h2>
            <a href="{{ route('admin.cms-pages.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                {{ __('New page') }}
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

            <form method="get" action="{{ route('admin.cms-pages.index') }}" class="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <x-input-label for="cms_pages_search" :value="__('Search CMS pages')" />
                    <x-text-input id="cms_pages_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" maxlength="100" autocomplete="off" placeholder="{{ __('Title or slug') }}" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                    @if (filled($search))
                        <a href="{{ route('admin.cms-pages.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Title') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Slug') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Lang') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Status') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Updated') }}</th>
                                <th class="pb-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pages as $p)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-gray-900">{{ $p->title }}</td>
                                    <td class="py-3 pe-4 font-mono text-xs text-gray-600">{{ $p->slug }}</td>
                                    <td class="py-3 pe-4 uppercase">{{ $p->locale }}</td>
                                    <td class="py-3 pe-4">
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs">{{ $p->status }}</span>
                                    </td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $p->updated_at->diffForHumans() }}</td>
                                    <td class="py-3 text-end space-x-2">
                                        <a href="{{ route('admin.cms-pages.preview', $p) }}?lang={{ $p->locale }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-900">{{ __('Preview') }}</a>
                                        <a href="{{ route('admin.cms-pages.edit', $p) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        <form action="{{ route('admin.cms-pages.destroy', $p) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this page?')));">
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
                                            {{ __('No CMS pages match your filters.') }}
                                        @else
                                            {{ __('No pages yet.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $pages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
