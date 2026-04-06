<x-app-layout>
    @php
        $orgLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    @endphp
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Applications for your organization') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Filter applications') }}</h3>
                </div>
                <form method="get" action="{{ route('organization.event-applications.index', $orgLocaleQ) }}" class="flex flex-wrap items-end gap-4 p-6">
                    <div>
                        <x-input-label for="org_app_search" :value="__('Search applications')" />
                        <x-text-input id="org_app_search" name="search" type="search" class="mt-1 block w-64 max-w-full" :value="$search" maxlength="100" autocomplete="off" placeholder="{{ __('Volunteer name or email') }}" />
                    </div>
                    <div>
                        <x-input-label for="org_filter_status" :value="__('Status')" />
                        <select id="org_filter_status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-48">
                            <option value="all" @selected($statusFilter === 'all')>{{ __('All statuses') }}</option>
                            <option value="{{ \App\Models\EventApplication::STATUS_PENDING }}" @selected($statusFilter === \App\Models\EventApplication::STATUS_PENDING)>{{ __('Application status pending') }}</option>
                            <option value="{{ \App\Models\EventApplication::STATUS_APPROVED }}" @selected($statusFilter === \App\Models\EventApplication::STATUS_APPROVED)>{{ __('Application status approved') }}</option>
                            <option value="{{ \App\Models\EventApplication::STATUS_REJECTED }}" @selected($statusFilter === \App\Models\EventApplication::STATUS_REJECTED)>{{ __('Application status rejected') }}</option>
                            <option value="{{ \App\Models\EventApplication::STATUS_WITHDRAWN }}" @selected($statusFilter === \App\Models\EventApplication::STATUS_WITHDRAWN)>{{ __('Application status withdrawn') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="org_filter_event_id" :value="__('Event')" />
                        <select id="org_filter_event_id" name="event_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:min-w-[14rem]">
                            <option value="">{{ __('All events') }}</option>
                            @foreach ($filterEvents as $fe)
                                <option value="{{ $fe->id }}" @selected((string) $eventId === (string) $fe->id)>{{ $fe->title_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="org_app_sort" :value="__('Sort applications')" />
                        <select id="org_app_sort" name="sort" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-52">
                            <option value="default" @selected($sort === 'default')>{{ __('Application sort pending first') }}</option>
                            <option value="submitted_desc" @selected($sort === 'submitted_desc')>{{ __('Application sort newest first') }}</option>
                            <option value="submitted_asc" @selected($sort === 'submitted_asc')>{{ __('Application sort oldest first') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                        @if (filled($search) || $statusFilter !== 'all' || $eventId !== null || $sort !== 'default')
                            <a href="{{ route('organization.event-applications.index', $orgLocaleQ) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear filters') }}</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500">
                                <th class="pb-3 pe-4 font-medium">{{ __('Event') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Volunteer') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Message') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Reviewer note') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Status') }}</th>
                                <th class="pb-3 pe-4 font-medium">{{ __('Submitted') }}</th>
                                <th class="pb-3 text-end font-medium">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($applications as $app)
                                <tr>
                                    <td class="py-3 pe-4 font-medium text-slate-900">
                                        @if ($app->event)
                                            <span>{{ $app->event->titleForLocale() }}</span>
                                            <div class="mt-1">
                                                <a href="{{ route('volunteer.opportunities.show', array_merge(['event' => $app->event], $orgLocaleQ)) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">{{ __('Public view') }} ↗</a>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 pe-4 text-slate-700">
                                        <div>{{ $app->user?->name ?? '—' }}</div>
                                        <div class="text-xs text-slate-500">{{ $app->user?->email }}</div>
                                    </td>
                                    <td class="max-w-xs py-3 pe-4 truncate text-slate-600" title="{{ $app->message }}">{{ $app->message ? \Illuminate\Support\Str::limit($app->message, 80) : '—' }}</td>
                                    <td class="max-w-xs py-3 pe-4 truncate text-slate-600" title="{{ $app->review_note }}">{{ $app->review_note ? \Illuminate\Support\Str::limit($app->review_note, 60) : '—' }}</td>
                                    <td class="py-3 pe-4">
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">
                                            @switch($app->status)
                                                @case(\App\Models\EventApplication::STATUS_PENDING)
                                                    {{ __('Application status pending') }}
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_APPROVED)
                                                    {{ __('Application status approved') }}
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_REJECTED)
                                                    {{ __('Application status rejected') }}
                                                    @break
                                                @case(\App\Models\EventApplication::STATUS_WITHDRAWN)
                                                    {{ __('Application status withdrawn') }}
                                                    @break
                                                @default
                                                    {{ $app->status }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap py-3 pe-4 text-slate-600">{{ $app->created_at->diffForHumans() }}</td>
                                    <td class="py-3 text-end align-top">
                                        @if ($app->isPending() && $canReviewApplications)
                                            <div class="flex flex-col items-end gap-2">
                                                <form action="{{ route('organization.event-applications.approve', array_merge(['event_application' => $app], $orgLocaleQ)) }}" method="post">
                                                    @csrf
                                                    <button type="submit" class="font-semibold text-emerald-700 hover:text-emerald-900">{{ __('Approve') }}</button>
                                                </form>
                                                <form action="{{ route('organization.event-applications.reject', array_merge(['event_application' => $app], $orgLocaleQ)) }}" method="post" class="w-full max-w-xs space-y-1" onsubmit="return confirm(@json(__('Reject this application?')));">
                                                    @csrf
                                                    <label for="org_reject_note_{{ $app->id }}" class="sr-only">{{ __('Note to volunteer (optional)') }}</label>
                                                    <textarea id="org_reject_note_{{ $app->id }}" name="review_note" rows="2" maxlength="1000" class="block w-full rounded-lg border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('Note to volunteer (optional)') }}"></textarea>
                                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-800">{{ __('Reject') }}</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-500">
                                        @if (filled($search) || $statusFilter !== 'all' || $eventId !== null || $sort !== 'default')
                                            {{ __('No applications match your filters.') }}
                                        @else
                                            {{ __('No applications yet.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
