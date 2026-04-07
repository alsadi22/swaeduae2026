@php
    $q = $adminLocaleQ ?? \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $siteQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $linkBase = 'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition duration-200 ease-out-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40';
    $linkInactive = 'text-slate-600 hover:bg-slate-100/80 hover:text-emerald-900';
    $linkActive = 'bg-emerald-50 text-emerald-900 shadow-sm ring-1 ring-emerald-100/90';
@endphp

<div class="flex h-full min-h-0 flex-col px-3 pb-4 pt-2">
    <div class="mb-4 flex items-center gap-2 border-b border-slate-200/80 pb-4">
        <a href="{{ route('admin.cms-pages.index', $q) }}" class="flex items-center gap-2 rounded-lg p-1 text-emerald-900 hover:bg-emerald-50/80" data-testid="admin-sidebar-brand">
            <x-application-logo class="block h-9 w-auto fill-current text-emerald-900" />
        </a>
        <div class="min-w-0 leading-tight">
            <a href="{{ route('admin.cms-pages.index', $q) }}" class="block truncate font-display text-sm font-bold text-slate-900 hover:text-emerald-900">{{ __('SwaedUAE') }}</a>
            <span class="text-xs font-medium text-slate-500">{{ __('Admin') }}</span>
        </div>
    </div>

    <nav class="min-h-0 flex-1 space-y-0.5 overflow-y-auto overscroll-contain" aria-label="{{ __('Admin navigation') }}">
        <a href="{{ route('admin.cms-pages.index', $q) }}" @class([$linkBase, request()->routeIs('admin.cms-pages.*') ? $linkActive : $linkInactive]) data-testid="admin-sidebar-cms">
            {{ __('CMS') }}
        </a>
        <a href="{{ route('admin.external-news-sources.index', $q) }}" @class([$linkBase, request()->routeIs('admin.external-news-sources.*') ? $linkActive : $linkInactive])>
            {{ __('News sources') }}
        </a>
        <a href="{{ route('admin.external-news-items.index', $q) }}" @class([$linkBase, request()->routeIs('admin.external-news-items.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('External news') }}</span>
                @if (($pendingExternalNewsItemsCount ?? 0) > 0)
                    <span data-testid="pending-external-news-nav-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $pendingExternalNewsItemsCount > 99 ? '99+' : $pendingExternalNewsItemsCount }}</span>
                @endif
            </span>
        </a>
        <a href="{{ route('admin.organizations.index', $q) }}" @class([$linkBase, request()->routeIs('admin.organizations.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('Organizations') }}</span>
                @if (($pendingOrganizationVerificationsCount ?? 0) > 0)
                    <span data-testid="pending-organizations-nav-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $pendingOrganizationVerificationsCount > 99 ? '99+' : $pendingOrganizationVerificationsCount }}</span>
                @endif
            </span>
        </a>
        <a href="{{ route('admin.events.index', $q) }}" @class([$linkBase, request()->routeIs('admin.events.*') ? $linkActive : $linkInactive])>
            {{ __('Events') }}
        </a>
        <a href="{{ route('admin.event-applications.index', $q) }}" @class([$linkBase, request()->routeIs('admin.event-applications.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('Applications') }}</span>
                @if (($pendingEventApplicationsCount ?? 0) > 0)
                    <span data-testid="pending-event-applications-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $pendingEventApplicationsCount > 99 ? '99+' : $pendingEventApplicationsCount }}</span>
                @endif
            </span>
        </a>
        <a href="{{ route('admin.disputes.index', $q) }}" @class([$linkBase, request()->routeIs('admin.disputes.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('Disputes') }}</span>
                @if (($openDisputesCount ?? 0) > 0)
                    <span data-testid="open-disputes-nav-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $openDisputesCount > 99 ? '99+' : $openDisputesCount }}</span>
                @endif
            </span>
        </a>
        <a href="{{ route('admin.checkin-attempts.index', $q) }}" @class([$linkBase, request()->routeIs('admin.checkin-attempts.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('Check-in log') }}</span>
                @if (($suspiciousCheckinAttemptsRecentCount ?? 0) > 0)
                    <span data-testid="suspicious-checkin-attempts-nav-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $suspiciousCheckinAttemptsRecentCount > 99 ? '99+' : $suspiciousCheckinAttemptsRecentCount }}</span>
                @endif
            </span>
        </a>
        <a href="{{ route('admin.flagged-attendance.index', $q) }}" @class([$linkBase, request()->routeIs('admin.flagged-attendance.*') ? $linkActive : $linkInactive])>
            <span class="inline-flex min-w-0 flex-1 items-center gap-1.5">
                <span class="truncate">{{ __('Flagged attendance') }}</span>
                @if (($flaggedAttendanceRowsCount ?? 0) > 0)
                    <span data-testid="flagged-attendance-nav-badge" class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $flaggedAttendanceRowsCount > 99 ? '99+' : $flaggedAttendanceRowsCount }}</span>
                @endif
            </span>
        </a>
    </nav>

    <div class="mt-4 space-y-0.5 border-t border-slate-200/80 pt-4">
        <a href="{{ route('home', $siteQ) }}" @class([$linkBase, $linkInactive]) data-testid="admin-sidebar-view-website">
            {{ __('View website') }}
        </a>
        <a href="{{ route('dashboard', $q) }}" @class([$linkBase, request()->routeIs('dashboard') ? $linkActive : $linkInactive]) data-testid="admin-sidebar-member-dashboard">
            {{ __('Member dashboard') }}
        </a>
        <a href="{{ route('profile.edit', $q) }}" @class([$linkBase, request()->routeIs('profile.*') ? $linkActive : $linkInactive])>
            {{ __('Profile') }}
        </a>
        <form method="POST" action="{{ route('logout', $q) }}">
            @csrf
            <button type="submit" class="{{ $linkBase }} {{ $linkInactive }} w-full text-start">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</div>
