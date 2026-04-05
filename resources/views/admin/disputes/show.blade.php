<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Dispute detail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="space-y-3 border-b border-gray-100 px-6 py-4">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ __('Event') }}:</span>
                        {{ $dispute->attendance?->event?->title_en ?? '—' }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ __('Volunteer') }}:</span>
                        {{ $dispute->attendance?->user?->name ?? '—' }} ({{ $dispute->attendance?->user?->email }})
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ __('Opened by') }}:</span>
                        {{ $dispute->openedBy?->name ?? '—' }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ __('Status') }}:</span>
                        @switch($dispute->status)
                            @case(\App\Models\Dispute::STATUS_OPEN)
                                {{ __('Dispute status open') }}
                                @break
                            @case(\App\Models\Dispute::STATUS_RESOLVED)
                                {{ __('Dispute status resolved') }}
                                @break
                            @case(\App\Models\Dispute::STATUS_DISMISSED)
                                {{ __('Dispute status dismissed') }}
                                @break
                            @default
                                {{ $dispute->status }}
                        @endswitch
                    </p>
                    @if ($dispute->attendance && $dispute->attendance->verifiedMinutes() !== null)
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">{{ __('Verified minutes') }}:</span>
                            {{ $dispute->attendance->verifiedMinutes() }}
                            @if ($dispute->attendance->minutes_adjustment !== null && $dispute->attendance->minutes_adjustment !== 0)
                                <span class="block text-xs text-gray-500">{{ __('Clock record') }} {{ $dispute->attendance->minutes_worked }} · {{ __('Adjustment') }} {{ $dispute->attendance->minutes_adjustment > 0 ? '+' : '' }}{{ $dispute->attendance->minutes_adjustment }}</span>
                            @endif
                        </p>
                    @endif
                </div>
                <div class="px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Description') }}</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-800">{{ $dispute->description }}</p>
                </div>
                @if ($dispute->resolved_at)
                    <div class="border-t border-gray-100 px-6 py-4">
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">{{ __('Resolution') }}:</span>
                            {{ $dispute->resolved_at->diffForHumans() }}
                            @if ($dispute->resolvedBy)
                                ({{ $dispute->resolvedBy->name }})
                            @endif
                        </p>
                        @if (filled($dispute->resolution_note))
                            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-800">{{ $dispute->resolution_note }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @can('resolve', $dispute)
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Resolution') }}</h3>
                    </div>
                    <form method="post" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-4 p-6">
                        @csrf
                        <div>
                            <x-input-label for="resolution" :value="__('Resolution')" />
                            <select id="resolution" name="resolution" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-md">
                                <option value="{{ \App\Models\Dispute::STATUS_RESOLVED }}">{{ __('Resolve as upheld') }}</option>
                                <option value="{{ \App\Models\Dispute::STATUS_DISMISSED }}">{{ __('Dismiss dispute') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="resolution_note" :value="__('Resolution note (optional)')" />
                            <textarea id="resolution_note" name="resolution_note" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('resolution_note') }}</textarea>
                        </div>
                        <x-primary-button type="submit">{{ __('Save resolution') }}</x-primary-button>
                    </form>
                </div>
            @endcan

            <div class="mt-6">
                <a href="{{ route('admin.disputes.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">{{ __('Back') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
