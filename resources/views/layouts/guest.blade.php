<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SwaedUAE') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page font-sans">
        <div class="relative flex min-h-screen flex-col items-center overflow-hidden pt-8 sm:justify-center sm:pt-0">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_120%_80%_at_50%_-20%,rgba(16,185,129,0.14),transparent_50%),radial-gradient(ellipse_80%_50%_at_100%_50%,rgba(180,134,11,0.06),transparent_45%)]" aria-hidden="true"></div>
            <div class="relative z-[1] w-full px-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 text-center transition hover:opacity-90">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-900 text-white shadow-card ring-1 ring-emerald-950/10" aria-hidden="true">
                        <svg class="h-8 w-8 opacity-95" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.09 6.26H21l-5.45 4.2 2.09 6.54L12 16.77 6.36 19l2.09-6.54L3 8.26h6.91L12 2z"/></svg>
                    </span>
                    <span class="font-display text-xl font-bold tracking-tight text-emerald-950">{{ __('SwaedUAE') }}</span>
                </a>
            </div>

            <div class="relative z-[1] mt-8 w-full overflow-hidden border border-slate-200/90 bg-white/95 px-6 py-8 shadow-card backdrop-blur-sm sm:mt-10 sm:max-w-md sm:rounded-2xl sm:px-8 sm:py-9">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
