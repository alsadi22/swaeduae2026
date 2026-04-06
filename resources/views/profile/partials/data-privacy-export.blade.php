@php
    $profileLocaleQ = $profileLocaleQ ?? \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
@endphp
<div class="max-w-xl">
    <header>
        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Data & privacy') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Account data export hint') }}</p>
    </header>
    @if (auth()->user()->hasVerifiedEmail())
        <div class="mt-4">
            <a href="{{ route('profile.data-export', $profileLocaleQ) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm hover:bg-slate-50">
                {{ __('Download my data (JSON)') }}
            </a>
            <p class="mt-2 text-xs text-slate-500">{{ __('Account data export rate hint') }}</p>
        </div>
    @else
        <p class="mt-4 text-sm text-amber-900">{{ __('Verify email to export data') }}</p>
    @endif
</div>
