<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ route('site.favicon', [], true) }}" type="image/svg+xml" sizes="any">
        <link rel="apple-touch-icon" href="{{ url('/favicon.svg') }}">
        <meta name="theme-color" content="#047857">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if (! empty($metaDescription))
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        <title>{{ $title ?? config('app.name', 'SwaedUAE') }}</title>
        <link rel="manifest" href="{{ route('site.webmanifest', absolute: true) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page font-sans" data-admin-shell="sidebar-v1">
        {{-- If you do not see a left sidebar on desktop, this server is not running the current app views (deploy / clear caches). --}}
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-emerald-900 focus:shadow-card focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-testid="skip-to-main-content">
            {{ __('Skip to main content') }}
        </a>
        <div
            class="min-h-screen lg:flex"
            x-data="{ sidebarOpen: false }"
            @keydown.window.escape="sidebarOpen = false"
        >
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
                @click="sidebarOpen = false"
                x-cloak
            ></div>

            <header class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-slate-200/90 bg-white/95 px-4 backdrop-blur-sm lg:hidden">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-700 hover:bg-slate-50 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40"
                    @click="sidebarOpen = true"
                    :aria-expanded="sidebarOpen"
                    data-testid="admin-sidebar-open"
                >
                    <span class="sr-only">{{ __('Open admin menu') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="truncate font-display text-sm font-bold text-slate-900">{{ __('Admin') }}</span>
            </header>

            <aside
                @click.capture="if ($event.target.closest('a,button')) { sidebarOpen = false }"
                class="fixed inset-y-0 start-0 z-50 flex min-h-screen w-[min(100%,18rem)] flex-col border-e border-slate-200 bg-slate-50 transition-transform duration-200 ease-out lg:static lg:z-0 lg:min-h-0 lg:w-64 lg:max-w-none lg:!translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'"
            >
                @include('layouts.admin-sidebar')
            </aside>

            <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:min-h-0">
                @isset($header)
                    <header class="hidden border-b border-slate-200/90 bg-white/90 shadow-sm backdrop-blur-sm lg:block">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                    <header class="border-b border-slate-200/90 bg-white/90 px-4 py-4 shadow-sm backdrop-blur-sm lg:hidden">
                        <div class="mx-auto max-w-7xl">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main id="main-content" tabindex="-1" class="min-w-0 flex-1 pb-12">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
