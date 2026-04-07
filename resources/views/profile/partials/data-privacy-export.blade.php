@php
    $profileLocaleQ = $profileLocaleQ ?? \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
@endphp
<div class="max-w-xl">
    <header>
        <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Data & privacy') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Account data export hint') }}</p>
    </header>
    <div class="mt-3">
        <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full [&_button]:border-slate-300 [&_button]:text-slate-700" test-id="profile-data-privacy-copy-page-url" />
    </div>
    @if (auth()->user()->hasVerifiedEmail())
        <div class="mt-4">
            <a href="{{ route('profile.data-export', $profileLocaleQ) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm hover:bg-slate-50">
                {{ __('Download my data (JSON)') }}
            </a>
            <p class="mt-2 text-xs text-slate-500">{{ __('Account data export rate hint') }}</p>
        </div>
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h4 class="text-sm font-bold text-slate-900">{{ __('Request account erasure') }}</h4>
            <p class="mt-1 text-sm text-slate-600">{{ __('Erasure request explanation') }}</p>
            <form method="post" action="{{ route('profile.erasure-request', $profileLocaleQ) }}" class="mt-4 space-y-3">
                @csrf
                @if (($dataPrivacyReturn ?? null) === 'volunteer_profile')
                    <input type="hidden" name="return" value="volunteer_profile" />
                @endif
                <div>
                    <x-input-label for="erasure_message" :value="__('Erasure request optional message')" />
                    <textarea id="erasure_message" name="message" rows="3" maxlength="2000" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('Note (optional)') }}"></textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <x-input-error :messages="$errors->get('erasure')" class="mt-2" />
                </div>
                <x-primary-button type="submit" data-testid="profile-erasure-request-submit">{{ __('Erasure request submit') }}</x-primary-button>
            </form>
        </div>
    @else
        <p class="mt-4 text-sm text-amber-900">{{ __('Verify email to export data') }}</p>
    @endif
</div>
