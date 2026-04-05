@props([
    'title' => null,
    'metaDescription' => null,
    'ogUrl' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogType' => 'website',
    'canonicalUrl' => null,
    'ogImage' => null,
    /** @var list<array{name: string, url: string}>|null */
    'breadcrumbItems' => null,
    /** @var array<string, mixed>|null */
    'extraJsonLd' => null,
])

@php
    $isAdminCmsPreview = request()->routeIs('admin.cms-pages.preview');
    $resolvedOgUrl = $ogUrl;
    if ($resolvedOgUrl === null && ! $isAdminCmsPreview) {
        $resolvedOgUrl = request()->fullUrl();
    }
    $resolvedOgTitle = $ogTitle ?? $title ?? config('app.name', 'SwaedUAE');
    $resolvedOgDescription = $ogDescription ?? $metaDescription ?? __('site.meta_description');
    $resolvedCanonical = $canonicalUrl;
    if ($resolvedCanonical === null && $resolvedOgUrl !== null && ! $isAdminCmsPreview) {
        $resolvedCanonical = $resolvedOgUrl;
    }
    $swaeduaeJsonLd = \App\Support\SwaedUaeStructuredData::publicLayoutGraph($isAdminCmsPreview);
    $breadcrumbJsonLd = null;
    if (is_array($breadcrumbItems) && $breadcrumbItems !== [] && ! $isAdminCmsPreview) {
        $breadcrumbJsonLd = \App\Support\SwaedUaeStructuredData::breadcrumbGraph($breadcrumbItems);
    }
    $resolvedExtraJsonLd = null;
    if (! $isAdminCmsPreview && is_array($extraJsonLd) && $extraJsonLd !== []) {
        $resolvedExtraJsonLd = $extraJsonLd;
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- Fingerprint for deploy checks: View Source and search "swaeduae-build". Missing or 0 means wrong docroot or no Vite build. --}}
        @php($swaeduaeBuild = is_readable(public_path('build/manifest.json')) ? (int) filemtime(public_path('build/manifest.json')) : 0)
        <meta name="swaeduae-build" content="{{ $swaeduaeBuild }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if ($metaDescription)
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        <title>{{ $title ?? config('app.name', 'SwaedUAE') }}</title>

        @if ($resolvedCanonical)
            <link rel="canonical" href="{{ $resolvedCanonical }}">
        @endif

        @if (! $isAdminCmsPreview)
            <link rel="alternate" hreflang="en" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">
            <link rel="alternate" hreflang="ar" href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}">
            <link rel="alternate" hreflang="x-default" href="{{ request()->fullUrlWithQuery(['lang' => config('app.locale', 'en')]) }}">
        @endif

        @if ($resolvedOgUrl)
            <meta property="og:url" content="{{ $resolvedOgUrl }}">
        @endif
        <meta property="og:title" content="{{ $resolvedOgTitle }}">
        <meta property="og:description" content="{{ $resolvedOgDescription }}">
        <meta property="og:type" content="{{ $ogType }}">
        @if (! $isAdminCmsPreview)
            <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_AE' : 'en_AE' }}">
            <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'ar' ? 'en_AE' : 'ar_AE' }}">
        @endif
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif

        @if ($ogImage)
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ $ogImage }}">
        @else
            <meta name="twitter:card" content="summary">
        @endif
        <meta name="twitter:title" content="{{ $resolvedOgTitle }}">
        <meta name="twitter:description" content="{{ $resolvedOgDescription }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if ($swaeduaeJsonLd !== null)
            <script type="application/ld+json">
                {!! json_encode($swaeduaeJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endif
        @if ($breadcrumbJsonLd !== null)
            <script type="application/ld+json">
                {!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endif
        @if ($resolvedExtraJsonLd !== null)
            <script type="application/ld+json">
                {!! json_encode($resolvedExtraJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endif
    </head>
    <body class="theme-page font-sans antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-emerald-900 focus:shadow-card focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
            {{ __('Skip to main content') }}
        </a>

        {{-- Utility strip: MOCE-style compact contact + language --}}
        <div class="border-b border-white/10 bg-institution-banner text-xs text-white/90">
            <div class="mx-auto flex max-w-content flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-2.5 sm:px-6">
                <p class="font-medium text-white/95">{{ __('Official bilingual site') }}</p>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    <a href="mailto:{{ config('swaeduae.mail.support') }}" class="hover:text-white hover:underline">{{ config('swaeduae.mail.support') }}</a>
                    <span class="hidden text-white/30 sm:inline" aria-hidden="true">|</span>
                    <div class="flex items-center gap-1 font-semibold sm:gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-md px-2 py-1 transition {{ app()->getLocale() === 'en' ? 'bg-white/10 text-institution-gold-light' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">English</a>
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="rounded-md px-2 py-1 transition {{ app()->getLocale() === 'ar' ? 'bg-white/10 text-institution-gold-light' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">العربية</a>
                    </div>
                </div>
            </div>
        </div>

        <header class="app-shell-header sticky top-0 z-40">
            <div class="mx-auto max-w-content px-4 sm:px-6" x-data="{ open: false }">
                <div class="flex h-[4.25rem] items-center justify-between gap-4 lg:h-20">
                    <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3 rounded-xl py-1 pe-2 outline-none transition hover:opacity-95 focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-900 text-white shadow-card ring-1 ring-emerald-950/15 transition group-hover:shadow-card-hover" aria-hidden="true">
                            <svg class="h-6 w-6 opacity-95" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2l2.09 6.26H21l-5.45 4.2 2.09 6.54L12 16.77 6.36 19l2.09-6.54L3 8.26h6.91L12 2z"/>
                            </svg>
                        </span>
                        <span class="min-w-0 text-start">
                            <span class="block truncate font-display text-base font-bold tracking-tight text-emerald-950 group-hover:text-emerald-800 sm:text-lg">{{ __('SwaedUAE') }}</span>
                            <span class="hidden truncate text-[11px] font-medium leading-tight text-slate-500 sm:block sm:text-xs">{{ __('SwaedUAE Association for Culture and Community Empowerment') }}</span>
                        </span>
                    </a>

                    <nav class="hidden flex-wrap items-center justify-end gap-1 lg:flex" aria-label="{{ __('Main navigation') }}">
                        <a href="{{ route('about') }}" class="public-nav-link {{ request()->routeIs('about') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('About') }}</a>
                        <a href="{{ route('leadership') }}" class="public-nav-link {{ request()->routeIs('leadership') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Leadership') }}</a>
                        <a href="{{ route('programs.index') }}" class="public-nav-link {{ request()->routeIs('programs.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Programs') }}</a>
                        <a href="{{ route('youth-councils') }}" class="public-nav-link {{ request()->routeIs('youth-councils') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Youth Councils') }}</a>
                        <a href="{{ route('events.index') }}" class="public-nav-link {{ request()->routeIs('events.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Events') }}</a>
                        <a href="{{ route('media.index') }}" class="public-nav-link {{ request()->routeIs('media.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Media') }}</a>
                        <a href="{{ route('gallery') }}" class="public-nav-link {{ request()->routeIs('gallery') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Gallery') }}</a>
                        <a href="{{ route('volunteer.index') }}" class="public-nav-link {{ request()->routeIs('volunteer.index') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Volunteer') }}</a>
                        @role('volunteer')
                            <a href="{{ route('volunteer.opportunities.index') }}" class="public-nav-link {{ request()->routeIs('volunteer.opportunities.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Opportunities') }}</a>
                        @endrole
                        <a href="{{ route('support.show') }}" class="public-nav-link {{ request()->routeIs('support.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Help and support') }}</a>
                        <a href="{{ route('contact.show') }}" class="public-nav-link {{ request()->routeIs('contact.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}">{{ __('Contact') }}</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="ms-2 inline-flex items-center rounded-xl border border-slate-200/80 bg-slate-50/90 px-3 py-2 text-sm font-bold text-slate-800 shadow-sm transition duration-200 hover:border-slate-300 hover:bg-white">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login', \App\Support\IntendedUrl::queryParamsForRequestUri(request())) }}" class="ms-2 public-nav-link public-nav-link--inactive">{{ __('Sign In') }}</a>
                            @if (Route::has('register.volunteer'))
                                <a href="{{ route('register.volunteer') }}" class="btn-primary-solid !px-3 !py-2 text-sm">{{ __('Join as Volunteer') }}</a>
                            @endif
                            @if (Route::has('register.organization'))
                                <a href="{{ route('register.organization') }}" class="ms-1 hidden text-sm font-bold text-emerald-900 underline decoration-emerald-700/40 underline-offset-2 hover:decoration-emerald-800 xl:inline">{{ __('Register Organization') }}</a>
                            @endif
                        @endauth
                    </nav>

                    <button type="button" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-600 transition duration-200 hover:bg-slate-100 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 lg:hidden" @click="open = ! open" :aria-expanded="open.toString()">
                        <span class="sr-only">{{ __('Menu') }}</span>
                        <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="border-t border-slate-100 bg-white/98 py-3 shadow-inner backdrop-blur-sm lg:hidden" x-cloak>
                    <div class="flex flex-col gap-0.5 text-sm font-semibold">
                        <a href="{{ route('about') }}" class="public-nav-link {{ request()->routeIs('about') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('About') }}</a>
                        <a href="{{ route('leadership') }}" class="public-nav-link {{ request()->routeIs('leadership') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Leadership') }}</a>
                        <a href="{{ route('programs.index') }}" class="public-nav-link {{ request()->routeIs('programs.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Programs') }}</a>
                        <a href="{{ route('youth-councils') }}" class="public-nav-link {{ request()->routeIs('youth-councils') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Youth Councils') }}</a>
                        <a href="{{ route('events.index') }}" class="public-nav-link {{ request()->routeIs('events.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Events') }}</a>
                        <a href="{{ route('media.index') }}" class="public-nav-link {{ request()->routeIs('media.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Media') }}</a>
                        <a href="{{ route('gallery') }}" class="public-nav-link {{ request()->routeIs('gallery') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Gallery') }}</a>
                        <a href="{{ route('volunteer.index') }}" class="public-nav-link {{ request()->routeIs('volunteer.index') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Volunteer') }}</a>
                        @role('volunteer')
                            <a href="{{ route('volunteer.opportunities.index') }}" class="public-nav-link {{ request()->routeIs('volunteer.opportunities.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Opportunities') }}</a>
                        @endrole
                        <a href="{{ route('support.show') }}" class="public-nav-link {{ request()->routeIs('support.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Help and support') }}</a>
                        <a href="{{ route('contact.show') }}" class="public-nav-link {{ request()->routeIs('contact.*') ? 'public-nav-link--active' : 'public-nav-link--inactive' }}" @click="open=false">{{ __('Contact') }}</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="public-nav-link public-nav-link--inactive mt-1 border-t border-slate-100 pt-2" @click="open=false">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login', \App\Support\IntendedUrl::queryParamsForRequestUri(request())) }}" class="public-nav-link public-nav-link--inactive mt-1 border-t border-slate-100 pt-2" @click="open=false">{{ __('Sign In') }}</a>
                            @if (Route::has('register.volunteer'))
                                <a href="{{ route('register.volunteer') }}" class="btn-primary-solid mx-0.5 mt-2 justify-center !py-2.5 text-sm" @click="open=false">{{ __('Join as Volunteer') }}</a>
                            @endif
                            @if (Route::has('register.organization'))
                                <a href="{{ route('register.organization') }}" class="public-nav-link public-nav-link--inactive mt-1" @click="open=false">{{ __('Register Organization') }}</a>
                            @endif
                        @endauth
                        <div class="mt-3 flex gap-3 border-t border-slate-100 pt-3 text-xs font-semibold text-slate-600">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-md px-2 py-1 hover:bg-slate-100">English</a>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="rounded-md px-2 py-1 hover:bg-slate-100">العربية</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="main-content" tabindex="-1">
            @if (is_array($breadcrumbItems) && $breadcrumbItems !== [])
                <div class="mx-auto max-w-content px-4 pt-8 sm:px-6">
                    <nav aria-label="{{ __('Breadcrumb') }}" class="text-sm">
                        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            @foreach ($breadcrumbItems as $index => $crumb)
                                <li class="inline-flex items-center gap-2">
                                    @if ($index > 0)
                                        <span class="select-none text-slate-300" aria-hidden="true">/</span>
                                    @endif
                                    @if ($index === count($breadcrumbItems) - 1)
                                        <span class="font-semibold text-slate-900" aria-current="page">{{ $crumb['name'] }}</span>
                                    @else
                                        <a href="{{ $crumb['url'] }}" class="font-medium text-emerald-800 hover:text-emerald-950 hover:underline">{{ $crumb['name'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
            @endif
            {{ $slot }}
        </main>

        <footer class="mt-20 border-t border-slate-200/90 bg-gradient-to-b from-white to-surface-page">
            <div class="mx-auto max-w-content px-4 py-14 sm:px-6">
                <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-12 lg:gap-10">
                    <div class="lg:col-span-4">
                        <p class="font-display text-lg font-bold text-emerald-950">{{ __('SwaedUAE') }}</p>
                        <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-600">{{ __('site.footer_tagline') }}</p>
                    </div>
                    @php($footerLocaleQ = \App\Support\PublicLocale::query())
                    <div class="lg:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Quick links') }}</p>
                        <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                            <li><a href="{{ route('programs.index', $footerLocaleQ) }}" class="footer-link">{{ __('Programs') }}</a></li>
                            <li><a href="{{ route('youth-councils', $footerLocaleQ) }}" class="footer-link">{{ __('Youth Councils') }}</a></li>
                            <li><a href="{{ route('events.index', $footerLocaleQ) }}" class="footer-link">{{ __('Events') }}</a></li>
                            <li><a href="{{ route('media.index', $footerLocaleQ) }}" class="footer-link">{{ __('Media') }}</a></li>
                            <li><a href="{{ route('gallery', $footerLocaleQ) }}" class="footer-link">{{ __('Gallery') }}</a></li>
                            <li><a href="{{ route('volunteer.index', $footerLocaleQ) }}" class="footer-link">{{ __('Volunteer') }}</a></li>
                        </ul>
                    </div>
                    <div class="lg:col-span-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Organization') }}</p>
                        <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                            <li><a href="{{ route('about', $footerLocaleQ) }}" class="footer-link">{{ __('About') }}</a></li>
                            <li><a href="{{ route('leadership', $footerLocaleQ) }}" class="footer-link">{{ __('Leadership') }}</a></li>
                            <li><a href="{{ route('partners', $footerLocaleQ) }}" class="footer-link">{{ __('Partners') }}</a></li>
                            <li><a href="{{ route('faq', $footerLocaleQ) }}" class="footer-link">{{ __('FAQ') }}</a></li>
                            <li><a href="{{ route('support.show', $footerLocaleQ) }}" class="footer-link">{{ __('Help and support') }}</a></li>
                            <li><a href="{{ route('contact.show', $footerLocaleQ) }}" class="footer-link">{{ __('Contact') }}</a></li>
                        </ul>
                    </div>
                    <div class="lg:col-span-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Information & support') }}</p>
                        <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                            <li><a href="mailto:{{ config('swaeduae.mail.support') }}" class="footer-link font-medium text-emerald-800 hover:text-emerald-900">{{ config('swaeduae.mail.support') }}</a></li>
                            <li><span class="text-slate-500">{{ config('swaeduae.domain') }}</span></li>
                            <li><a href="{{ route('legal.terms', $footerLocaleQ) }}" class="footer-link">{{ __('Terms') }}</a></li>
                            <li><a href="{{ route('legal.privacy', $footerLocaleQ) }}" class="footer-link">{{ __('Privacy') }}</a></li>
                            <li><a href="{{ route('legal.cookies', $footerLocaleQ) }}" class="footer-link">{{ __('Cookies') }}</a></li>
                            <li><a href="{{ route('sitemap') }}" class="footer-link">{{ __('Sitemap') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 flex flex-col gap-4 border-t border-slate-100 pt-8 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} {{ __('SwaedUAE Association for Culture and Community Empowerment') }}</p>
                    <p class="text-slate-400">{{ __('Last updated') }} {{ date('d/m/Y') }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
