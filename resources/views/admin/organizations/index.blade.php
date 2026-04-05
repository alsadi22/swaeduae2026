<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Organizations') }}
            </h2>
            <a href="{{ route('admin.organizations.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                {{ __('New organization') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form method="get" action="{{ route('admin.organizations.index') }}" class="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                @if ($filter !== 'all')
                    <input type="hidden" name="verification" value="{{ $filter }}">
                @endif
                <div>
                    <x-input-label for="admin_orgs_search" :value="__('Search organizations')" />
                    <x-text-input id="admin_orgs_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" maxlength="100" autocomplete="off" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                    @if (filled($search))
                        <a href="{{ route('admin.organizations.index', array_filter(['verification' => $filter !== 'all' ? $filter : null])) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            </form>

            <div class="mb-4 flex flex-wrap items-center gap-3 text-sm">
                <span class="font-medium text-gray-600">{{ __('Filter') }}:</span>
                <a href="{{ route('admin.organizations.index', array_filter(['verification' => 'all', 'search' => $search ?: null])) }}" class="rounded-md px-2 py-1 hover:bg-gray-100 {{ $filter === 'all' ? 'bg-gray-200 font-semibold' : '' }}">{{ __('All') }}</a>
                <a href="{{ route('admin.organizations.index', array_filter(['verification' => 'pending', 'search' => $search ?: null])) }}" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:bg-gray-100 {{ $filter === 'pending' ? 'bg-amber-100 font-semibold text-amber-950' : '' }}">
                    {{ __('Pending verification') }}
                    @if ($pendingCount > 0)
                        <span data-testid="pending-organizations-badge" class="rounded-full bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-950">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.organizations.index', array_filter(['verification' => 'approved', 'search' => $search ?: null])) }}" class="rounded-md px-2 py-1 hover:bg-gray-100 {{ $filter === 'approved' ? 'bg-gray-200 font-semibold' : '' }}">{{ __('Approved') }}</a>
                <a href="{{ route('admin.organizations.index', array_filter(['verification' => 'rejected', 'search' => $search ?: null])) }}" class="rounded-md px-2 py-1 hover:bg-gray-100 {{ $filter === 'rejected' ? 'bg-gray-200 font-semibold' : '' }}">{{ __('Rejected') }}</a>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Name (English)') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Name (Arabic)') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Verification status') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Events') }}</th>
                                <th class="pb-3 text-end font-medium">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($organizations as $org)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-gray-900">{{ $org->name_en }}</td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $org->name_ar ?? '—' }}</td>
                                    <td class="py-3 pe-4 text-gray-700">
                                        @if ($org->isPendingVerification())
                                            <span class="font-semibold text-amber-800">{{ __('Pending verification') }}</span>
                                        @elseif ($org->isRejected())
                                            <span class="font-semibold text-red-800">{{ __('Rejected') }}</span>
                                        @else
                                            <span class="text-emerald-800">{{ __('Approved') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pe-4 text-gray-600">{{ $org->events_count }}</td>
                                    <td class="py-3 text-end">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @can('approve', $org)
                                                <form action="{{ route('admin.organizations.approve', $org) }}" method="post" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">{{ __('Approve') }}</button>
                                                </form>
                                            @endcan
                                            @can('reject', $org)
                                                <form action="{{ route('admin.organizations.reject', $org) }}" method="post" class="inline-flex max-w-xs flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="review_note" placeholder="{{ __('Rejection note (optional)') }}" class="min-w-[8rem] rounded border-gray-300 text-xs shadow-sm" />
                                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-900" onclick="return confirm(@json(__('Reject this organization registration?')));">{{ __('Reject') }}</button>
                                                </form>
                                            @endcan
                                            <a href="{{ route('admin.organizations.edit', $org) }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                            <form action="{{ route('admin.organizations.destroy', $org) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this organization?')));">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        @if (filled($search) || $filter !== 'all')
                                            {{ __('No organizations match your filters.') }}
                                        @else
                                            {{ __('No organizations yet.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $organizations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
