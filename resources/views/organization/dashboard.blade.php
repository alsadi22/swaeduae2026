<x-app-layout>
    @php
        $orgLocaleQ = \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    @endphp
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Organization dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
            @endif

            @if (! $organization)
                <div class="overflow-hidden border border-amber-200 bg-amber-50/90 p-6 shadow-sm sm:rounded-lg">
                    <p class="font-medium text-amber-950">{{ __('Organization dashboard missing link') }}</p>
                    <p class="mt-2 text-sm text-amber-900/90">{{ __('Organization dashboard missing link hint') }}</p>
                </div>
            @elseif ($organization->isPendingVerification())
                <div class="overflow-hidden border border-amber-200 bg-amber-50/90 p-6 shadow-sm sm:rounded-lg">
                    <p class="font-display text-lg font-bold text-amber-950">{{ __('Organization pending verification title') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-amber-900/90">{{ __('Organization pending verification body') }}</p>
                    <p class="mt-4 text-sm text-amber-900/80">
                        <span class="font-semibold">{{ __('Organization name label') }}:</span>
                        {{ $organization->nameForLocale() }}
                    </p>
                </div>
            @elseif ($organization->isRejected())
                <div class="overflow-hidden border border-red-200 bg-red-50/90 p-6 shadow-sm sm:rounded-lg">
                    <p class="font-display text-lg font-bold text-red-950">{{ __('Organization registration rejected title') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-red-900/90">{{ __('Organization registration rejected body') }}</p>
                    @if (filled($organization->verification_review_note))
                        <div class="mt-4 rounded-lg border border-red-100 bg-white/80 p-4 text-sm text-red-950 whitespace-pre-wrap">{{ $organization->verification_review_note }}</div>
                    @endif
                    <p class="mt-4 text-sm text-red-900/80">
                        <a href="{{ route('contact.show', $orgLocaleQ) }}" class="font-bold underline hover:text-red-950">{{ __('Contact') }}</a>
                    </p>
                    @if (auth()->user()->hasRole('org-owner'))
                        <div class="mt-8 border-t border-red-200/80 pt-6">
                            <h3 class="font-display text-base font-bold text-red-950">{{ __('Resubmit for review') }}</h3>
                            <p class="mt-1 text-sm text-red-900/85">{{ __('Organization resubmit hint') }}</p>
                            <form method="post" action="{{ route('organization.verification-resubmit', $orgLocaleQ) }}" class="mt-4 space-y-4 max-w-lg">
                                @csrf
                                <div>
                                    <x-input-label for="resubmit_name_en" :value="__('Organization name (English)')" />
                                    <x-text-input id="resubmit_name_en" name="name_en" type="text" class="mt-1 block w-full" :value="old('name_en', $organization->name_en)" required maxlength="255" />
                                    <x-input-error class="mt-2" :messages="$errors->get('name_en')" />
                                </div>
                                <div>
                                    <x-input-label for="resubmit_name_ar" :value="__('Organization name (Arabic, optional)')" />
                                    <x-text-input id="resubmit_name_ar" name="name_ar" type="text" class="mt-1 block w-full" :value="old('name_ar', $organization->name_ar)" maxlength="255" />
                                    <x-input-error class="mt-2" :messages="$errors->get('name_ar')" />
                                </div>
                                <x-primary-button type="submit">{{ __('Resubmit for review') }}</x-primary-button>
                            </form>
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-6">
                    @if ($canViewOrganizationEvents)
                        <div class="overflow-hidden border border-slate-200 bg-white p-6 shadow-sm sm:rounded-lg sm:p-8">
                            <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Organization portal events title') }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ __('Organization portal events dashboard hint') }}</p>
                            <p class="mt-3 text-sm text-slate-700">
                                <span class="font-semibold text-slate-800">{{ __('Upcoming events') }}:</span>
                                <span data-testid="org-dashboard-upcoming-events-count">{{ $upcomingOrganizationEventsCount }}</span>
                                <span class="text-slate-500">{{ __('not ended yet') }}</span>
                            </p>
                            <div class="mt-4 flex flex-wrap gap-4">
                                <a href="{{ route('organization.events.index', $orgLocaleQ) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-800 hover:text-emerald-950 hover:underline">
                                    {{ __('Open organization events list') }}
                                    →
                                </a>
                                <a href="{{ route('organization.events.index', array_merge($orgLocaleQ, ['timing' => 'upcoming', 'sort' => 'starts_asc'])) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 hover:text-emerald-950 hover:underline">
                                    {{ __('View upcoming events only') }}
                                    →
                                </a>
                            </div>
                        </div>
                    @endif
                    @if ($canViewOrganizationApplications)
                        <div class="overflow-hidden border border-slate-200 bg-white p-6 shadow-sm sm:rounded-lg sm:p-8">
                            <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Organization portal volunteer applications') }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ __('Organization portal volunteer applications hint') }}</p>
                            <a href="{{ route('organization.event-applications.index', $orgLocaleQ) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-800 hover:text-emerald-950 hover:underline">
                                {{ __('Open applications list') }}
                                @if ($pendingOrganizationApplicationsCount > 0)
                                    <span data-testid="org-dashboard-pending-applications-badge" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-900">{{ $pendingOrganizationApplicationsCount > 99 ? '99+' : $pendingOrganizationApplicationsCount }} {{ __('pending') }}</span>
                                @endif
                                →
                            </a>
                        </div>
                    @endif
                    @if ($canInviteStaff)
                        <div class="overflow-hidden border border-slate-200 bg-white p-6 shadow-sm sm:rounded-lg sm:p-8">
                            <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Invite organization staff') }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ __('Invite organization staff hint') }}</p>

                            <form action="{{ route('organization.invitations.store', $orgLocaleQ) }}" method="post" class="mt-6 space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="invite_email" :value="__('Email')" />
                                    <x-text-input id="invite_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="email" />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                </div>
                                <div>
                                    <x-input-label for="invite_role" :value="__('Role')" />
                                    <select id="invite_role" name="role" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                                        <option value="{{ \App\Models\OrganizationInvitation::ROLE_MANAGER }}" @selected(old('role') === \App\Models\OrganizationInvitation::ROLE_MANAGER)>{{ __('invitation.role.org-manager') }}</option>
                                        <option value="{{ \App\Models\OrganizationInvitation::ROLE_COORDINATOR }}" @selected(old('role', \App\Models\OrganizationInvitation::ROLE_COORDINATOR) === \App\Models\OrganizationInvitation::ROLE_COORDINATOR)>{{ __('invitation.role.org-coordinator') }}</option>
                                        <option value="{{ \App\Models\OrganizationInvitation::ROLE_VIEWER }}" @selected(old('role') === \App\Models\OrganizationInvitation::ROLE_VIEWER)>{{ __('invitation.role.org-viewer') }}</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                                </div>
                                <x-primary-button type="submit">{{ __('Send invitation') }}</x-primary-button>
                            </form>

                            <div class="mt-8 border-t border-slate-100 pt-6">
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Open invitations') }}</h4>
                                    <form method="get" action="{{ route('organization.dashboard', $orgLocaleQ) }}" class="mt-3 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                                        <div class="min-w-0 flex-1">
                                            <label for="invitation_search" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Search invitations by email') }}</label>
                                            <input type="search" id="invitation_search" name="invitation_search" value="{{ $invitationSearch }}" maxlength="100" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:max-w-md" />
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <x-primary-button type="submit">{{ __('Apply filters') }}</x-primary-button>
                                            <a href="{{ route('organization.dashboard', $orgLocaleQ) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Clear filters') }}</a>
                                        </div>
                                    </form>
                                    @if ($pendingInvitations->isEmpty())
                                        <p class="mt-4 text-sm text-slate-600">
                                            @if (filled($invitationSearch))
                                                {{ __('No invitations match your filters.') }}
                                            @else
                                                {{ __('No pending invitations.') }}
                                            @endif
                                        </p>
                                    @else
                                        <ul class="mt-4 divide-y divide-slate-100 text-sm">
                                            @foreach ($pendingInvitations as $inv)
                                                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                                    <div>
                                                        <p class="font-medium text-slate-900">{{ $inv->email }}</p>
                                                        <p class="text-xs {{ $inv->isExpired() ? 'text-amber-700' : 'text-slate-500' }}">
                                                            {{ __('invitation.role.'.$inv->role) }}
                                                            @if ($inv->isExpired())
                                                                · {{ __('Invitation expired') }}
                                                            @else
                                                                · {{ __('Expires') }} {{ $inv->expires_at->locale(app()->getLocale())->isoFormat('LLL') }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        @can('resend', $inv)
                                                            <form action="{{ route('organization.invitations.resend', array_merge(['invitation' => $inv], $orgLocaleQ)) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">{{ __('Resend invitation') }}</button>
                                                            </form>
                                                        @endcan
                                                        <form action="{{ route('organization.invitations.destroy', array_merge(['invitation' => $inv], $orgLocaleQ)) }}" method="post" onsubmit="return confirm(@json(__('Cancel this invitation?')));">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800">{{ __('Cancel') }}</button>
                                                        </form>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                        </div>
                    @endif

                    <div class="overflow-hidden border border-slate-200 bg-white p-8 shadow-sm sm:rounded-lg">
                        <p class="leading-relaxed text-slate-700">{{ __('site.organization_dashboard_placeholder') }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ __('site.organization_dashboard_docs') }}</p>
                        <p class="mt-6 text-sm text-slate-600">
                            <span class="font-semibold text-slate-800">{{ __('Organization name label') }}:</span>
                            {{ $organization->nameForLocale() }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
