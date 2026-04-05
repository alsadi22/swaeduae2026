<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Open dispute') }}
        </h2>
    </x-slot>

    @php
        $attLocaleQ = \App\Support\PublicLocale::queryForUser(auth()->user());
    @endphp
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-surface overflow-hidden">
                <div class="border-b border-slate-200/80 px-6 py-4">
                    @if ($attendance->event)
                        <h3 class="font-display text-lg font-bold text-slate-900">{{ $attendance->event->titleForLocale() }}</h3>
                        @if ($attendance->event->organization)
                            <p class="text-sm text-slate-500">{{ $attendance->event->organization->nameForLocale() }}</p>
                        @endif
                    @endif
                    <p class="mt-2 text-sm text-slate-600">{{ __('Dispute attendance hint') }}</p>
                </div>
                <form method="post" action="{{ route('dashboard.attendance.disputes.store', array_merge(['attendance' => $attendance], $attLocaleQ)) }}" class="space-y-4 p-6">
                    @csrf
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="8" required minlength="20" maxlength="5000" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-primary-button type="submit">{{ __('Submit dispute') }}</x-primary-button>
                        <a href="{{ route('dashboard.attendance.index', $attLocaleQ) }}" class="btn-secondary-muted">{{ __('Back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
