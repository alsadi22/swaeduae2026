<x-app-layout>
    @php
        $orgLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    @endphp
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organization portal event roster title') }}
            </h2>
            <a href="{{ route('organization.events.index', $orgLocaleQ) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                {{ __('Back to events') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-6">
                <p class="text-sm font-medium text-gray-900">{{ $event->title_en }}</p>
                @if (filled($event->title_ar))
                    <p class="mt-1 text-sm text-gray-600" dir="rtl">{{ $event->title_ar }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Event starts') }}:
                    <span class="font-medium text-gray-800">{{ $event->event_starts_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</span>
                </p>
                <p class="mt-1 text-sm text-gray-600">{{ __('Organization portal event roster hint') }}</p>
            </div>

            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <form method="get" action="{{ route('organization.events.roster', array_merge(['event' => $event], $orgLocaleQ)) }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="roster_search" :value="__('Search roster')" />
                        <x-text-input id="roster_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" autocomplete="off" />
                    </div>
                    <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
                    @if (filled($search))
                        <a href="{{ route('organization.events.roster', array_merge(['event' => $event], $orgLocaleQ)) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Clear') }}</a>
                    @endif
                </form>
                <div class="flex flex-col items-end gap-1">
                    <a href="{{ route('organization.events.roster.export', \App\Support\PublicLocale::mergeQuery(array_filter(['event' => $event, 'search' => filled($search) ? $search : null]))) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        {{ __('Export roster CSV') }}
                    </a>
                    @if (filled($search))
                        <p class="max-w-xs text-end text-xs text-gray-500">{{ __('Organization portal roster export filtered hint') }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Name') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Email') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Joined roster') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Status') }}</th>
                                <th class="pb-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($volunteers as $v)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-gray-900">{{ $v->name }}</td>
                                    <td class="py-3 pe-4 text-gray-700">{{ $v->email }}</td>
                                    <td class="py-3 text-gray-600 whitespace-nowrap">
                                        @if ($v->pivot?->created_at)
                                            {{ $v->pivot->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 text-gray-600">
                                        @php
                                            $att = $attendanceByUserId->get($v->id);
                                        @endphp
                                        @if ($att === null)
                                            <span class="text-gray-400">—</span>
                                        @else
                                            {{ \App\Models\Attendance::localizedStateLabel($att->state) }}
                                            @if ($att->state === \App\Models\Attendance::STATE_CHECKED_OUT && $att->minutes_worked !== null)
                                                <div class="mt-0.5 text-xs text-gray-500">{{ __('Verified minutes') }}: {{ $att->verifiedMinutes() }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3 text-end">
                                        @can('removeVolunteerFromRosterInOrganizationPortal', [$event, $v])
                                            <form action="{{ route('organization.events.roster.volunteers.destroy', array_merge(['event' => $event, 'volunteer' => $v], $orgLocaleQ)) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Remove volunteer from roster confirm')));">
                                                @csrf
                                                @method('delete')
                                                @if (filled($search))
                                                    <input type="hidden" name="search" value="{{ $search }}" />
                                                @endif
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">{{ __('Remove from roster') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">{{ __('No volunteers on the roster yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $volunteers->links() }}
                    </div>
                </div>
            </div>

            @can('configureInOrganizationPortal', $event)
                <p class="mt-6 text-center text-sm">
                    <a href="{{ route('organization.events.edit', array_merge(['event' => $event], $orgLocaleQ)) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('Edit event') }}</a>
                </p>
            @endcan
        </div>
    </div>
</x-app-layout>
