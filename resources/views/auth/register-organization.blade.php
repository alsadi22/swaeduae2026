@php($orgRegLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user()))
<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="font-display text-xl font-bold text-emerald-950">{{ __('Register Organization') }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ __('Organization registration subtitle') }}</p>
    </div>

    <p class="mb-6 text-sm leading-relaxed text-slate-600">{{ __('site.register_organization_intro') }}</p>

    <form method="POST" action="{{ route('register.organization.store', $orgRegLocaleQ) }}">
        @csrf

        <div class="mb-2 font-display text-sm font-bold text-slate-800">{{ __('Organization details') }}</div>
        <div class="mt-3">
            <x-input-label for="name_en" :value="__('Organization name (English)')" />
            <x-text-input id="name_en" class="mt-1 block w-full" type="text" name="name_en" :value="old('name_en')" required autocomplete="organization" />
            <x-input-error :messages="$errors->get('name_en')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="name_ar" :value="__('Organization name (Arabic, optional)')" />
            <x-text-input id="name_ar" class="mt-1 block w-full" type="text" name="name_ar" :value="old('name_ar')" autocomplete="organization" />
            <x-input-error :messages="$errors->get('name_ar')" class="mt-2" />
        </div>

        <div class="mb-2 mt-8 font-display text-sm font-bold text-slate-800">{{ __('Representative account') }}</div>
        <p class="mb-3 text-xs text-slate-500">{{ __('Organization representative account hint') }}</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input id="first_name" class="mt-1 block w-full" type="text" name="first_name" :value="old('first_name')" required autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input id="last_name" class="mt-1 block w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Mobile number')" />
            <x-text-input id="phone" class="mt-1 block w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="locale_preferred" :value="__('Preferred language')" />
            <select id="locale_preferred" name="locale_preferred" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                <option value="en" @selected(old('locale_preferred', 'en') === 'en')>English</option>
                <option value="ar" @selected(old('locale_preferred') === 'ar')>العربية</option>
            </select>
            <x-input-error :messages="$errors->get('locale_preferred')" class="mt-2" />
        </div>

        <div class="mt-6">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="terms" value="1" class="mt-1 rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" @checked(old('terms')) required />
                <span class="text-sm text-slate-700">
                    {{ __('I agree to the') }}
                    <a href="{{ route('legal.terms', $orgRegLocaleQ) }}" target="_blank" rel="noopener" class="font-semibold text-emerald-800 underline">{{ __('Terms') }}</a>
                    {{ __('and') }}
                    <a href="{{ route('legal.privacy', $orgRegLocaleQ) }}" target="_blank" rel="noopener" class="font-semibold text-emerald-800 underline">{{ __('Privacy') }}</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm text-slate-600 underline hover:text-emerald-900" href="{{ route('login', array_merge(\App\Support\IntendedUrl::queryParamsForRelativeUri((string) request()->query('return', '')), $orgRegLocaleQ)) }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Submit organization registration') }}</x-primary-button>
        </div>

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ route('register.volunteer', $orgRegLocaleQ) }}" class="font-semibold text-emerald-800 hover:underline">{{ __('Join as Volunteer') }}</a>
        </p>
    </form>
</x-guest-layout>
