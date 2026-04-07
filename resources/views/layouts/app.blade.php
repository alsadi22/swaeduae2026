<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ route('site.favicon', [], true) }}" type="image/svg+xml" sizes="any">
        <link rel="apple-touch-icon" href="{{ url('/favicon.svg') }}">
        <meta name="theme-color" content="#047857">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if ($metaDescription)
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        <title>{{ $title ?? config('app.name', 'SwaedUAE') }}</title>
        <link rel="manifest" href="{{ route('site.webmanifest', absolute: true) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page font-sans">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-emerald-900 focus:shadow-card focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-testid="skip-to-main-content">
            {{ __('Skip to main content') }}
        </a>
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-slate-200/90 bg-white/90 shadow-sm backdrop-blur-sm">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main id="main-content" tabindex="-1" class="pb-12">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
