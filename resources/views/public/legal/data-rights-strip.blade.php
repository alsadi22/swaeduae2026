@php
    $drLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
@endphp
<aside
    class="mt-10 rounded-xl border border-emerald-100 bg-emerald-50/60 p-6 text-sm text-slate-700"
    data-testid="legal-data-rights-strip"
    aria-label="{{ __('Your data and account') }}"
>
    <h2 class="font-display text-base font-bold text-emerald-950">{{ __('Your data and account') }}</h2>
    <ul class="mt-3 list-disc space-y-2 ps-5">
        <li>
            <a href="{{ route('support.show', $drLocaleQ) }}" class="font-semibold text-emerald-900 hover:underline">{{ __('Help and support') }}</a>
            <span class="text-slate-500"> — {{ __('site.legal_data_rights_support_hint') }}</span>
        </li>
        <li>
            <a href="{{ route('legal.privacy', $drLocaleQ) }}" class="font-semibold text-emerald-900 hover:underline">{{ __('Privacy policy') }}</a>
        </li>
        @auth
            <li>
                <a href="{{ route('profile.edit', $drLocaleQ) }}" class="font-semibold text-emerald-900 hover:underline">{{ __('Profile & data export') }}</a>
            </li>
        @else
            <li>
                <a href="{{ route('login', $drLocaleQ) }}" class="font-semibold text-emerald-900 hover:underline">{{ __('Log in for profile and data export') }}</a>
            </li>
        @endauth
    </ul>
</aside>
