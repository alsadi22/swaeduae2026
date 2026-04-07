<nav x-data="{ open: false }" class="app-shell-header sticky top-0 z-50">
    @php($appNavLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user()))
    @php($isAdminShellUser = auth()->check() && auth()->user()->hasAnyRole(['admin', 'super-admin']))
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $isAdminShellUser ? route('admin.cms-pages.index', $appNavLocaleQ) : route('home', $appNavLocaleQ) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-emerald-900" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden gap-1 sm:-my-px sm:ms-8 sm:flex sm:items-center lg:gap-1.5">
                    @unless ($isAdminShellUser)
                        <x-nav-link :href="route('home', $appNavLocaleQ)" :active="request()->routeIs('home')">
                            {{ __('Home') }}
                        </x-nav-link>
                        <x-nav-link :href="route('volunteer.index', $appNavLocaleQ)" :active="request()->routeIs('volunteer.index')">
                            {{ __('Volunteer') }}
                        </x-nav-link>
                    @endunless
                    @if ($isAdminShellUser)
                        <x-nav-link :href="route('admin.cms-pages.index', $appNavLocaleQ)" :active="request()->routeIs('admin.*')">
                            {{ __('Admin panel') }}
                        </x-nav-link>
                    @endif
                    @role('volunteer')
                        <x-nav-link :href="route('volunteer.opportunities.index', $appNavLocaleQ)" :active="request()->routeIs('volunteer.opportunities.*')">
                            {{ __('Opportunities') }}
                        </x-nav-link>
                        <x-nav-link :href="route('volunteer.profile.edit', $appNavLocaleQ)" :active="request()->routeIs('volunteer.profile.*')">
                            {{ __('Volunteer profile') }}
                        </x-nav-link>
                        <x-nav-link :href="route('dashboard.attendance.index', $appNavLocaleQ)" :active="request()->routeIs('dashboard.attendance.*')">
                            {{ __('My attendance') }}
                        </x-nav-link>
                    @endrole
                    <x-nav-link :href="route('dashboard', $appNavLocaleQ)" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @hasanyrole('org-owner|org-manager|org-coordinator|org-viewer')
                        <x-nav-link :href="route('organization.dashboard', $appNavLocaleQ)" :active="request()->routeIs('organization.dashboard')">
                            {{ __('Organization dashboard') }}
                        </x-nav-link>
                    @endhasanyrole
                    @can('view-organization-events')
                        <x-nav-link :href="route('organization.events.index', $appNavLocaleQ)" :active="request()->routeIs('organization.events.*')">
                            {{ __('Organization portal events nav') }}
                        </x-nav-link>
                    @endcan
                    @can('view-organization-event-applications')
                        <x-nav-link :href="route('organization.event-applications.index', $appNavLocaleQ)" :active="request()->routeIs('organization.event-applications.*')">
                            <span class="inline-flex items-center gap-1.5">
                                {{ __('Organization portal applications nav') }}
                                @if (($pendingOrganizationEventApplicationsCount ?? 0) > 0)
                                    <span data-testid="org-pending-applications-nav-badge" class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-900">{{ $pendingOrganizationEventApplicationsCount > 99 ? '99+' : $pendingOrganizationEventApplicationsCount }}</span>
                                @endif
                            </span>
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center rounded-xl border border-slate-200/80 bg-slate-50/90 px-3 py-2 text-sm font-semibold text-slate-700 transition duration-200 hover:border-slate-300 hover:bg-white hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/35">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if ($isAdminShellUser)
                            <x-dropdown-link :href="route('home', $appNavLocaleQ)">
                                {{ __('View website') }}
                            </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('profile.edit', $appNavLocaleQ)">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout', $appNavLocaleQ) }}">
                            @csrf

                            <x-dropdown-link :href="route('logout', $appNavLocaleQ)"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 transition duration-200 hover:bg-slate-100 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @unless ($isAdminShellUser)
                <x-responsive-nav-link :href="route('home', $appNavLocaleQ)" :active="request()->routeIs('home')">
                    {{ __('Home') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('volunteer.index', $appNavLocaleQ)" :active="request()->routeIs('volunteer.index')">
                    {{ __('Volunteer') }}
                </x-responsive-nav-link>
            @endunless
            @if ($isAdminShellUser)
                <x-responsive-nav-link :href="route('admin.cms-pages.index', $appNavLocaleQ)" :active="request()->routeIs('admin.*')">
                    {{ __('Admin panel') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('home', $appNavLocaleQ)" :active="request()->routeIs('home')">
                    {{ __('View website') }}
                </x-responsive-nav-link>
            @endif
            @role('volunteer')
                <x-responsive-nav-link :href="route('volunteer.opportunities.index', $appNavLocaleQ)" :active="request()->routeIs('volunteer.opportunities.*')">
                    {{ __('Opportunities') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('volunteer.profile.edit', $appNavLocaleQ)" :active="request()->routeIs('volunteer.profile.*')">
                    {{ __('Volunteer profile') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dashboard.attendance.index', $appNavLocaleQ)" :active="request()->routeIs('dashboard.attendance.*')">
                    {{ __('My attendance') }}
                </x-responsive-nav-link>
            @endrole
            <x-responsive-nav-link :href="route('dashboard', $appNavLocaleQ)" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @hasanyrole('org-owner|org-manager|org-coordinator|org-viewer')
                <x-responsive-nav-link :href="route('organization.dashboard', $appNavLocaleQ)" :active="request()->routeIs('organization.dashboard')">
                    {{ __('Organization dashboard') }}
                </x-responsive-nav-link>
            @endhasanyrole
            @can('view-organization-events')
                <x-responsive-nav-link :href="route('organization.events.index', $appNavLocaleQ)" :active="request()->routeIs('organization.events.*')">
                    {{ __('Organization portal events nav') }}
                </x-responsive-nav-link>
            @endcan
            @can('view-organization-event-applications')
                <x-responsive-nav-link :href="route('organization.event-applications.index', $appNavLocaleQ)" :active="request()->routeIs('organization.event-applications.*')">
                    <span class="inline-flex items-center gap-2">
                        {{ __('Organization portal applications nav') }}
                        @if (($pendingOrganizationEventApplicationsCount ?? 0) > 0)
                            <span data-testid="org-pending-applications-nav-badge-mobile" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-900">{{ $pendingOrganizationEventApplicationsCount > 99 ? '99+' : $pendingOrganizationEventApplicationsCount }}</span>
                        @endif
                    </span>
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-slate-200 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit', $appNavLocaleQ)">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout', $appNavLocaleQ) }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout', $appNavLocaleQ)"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
