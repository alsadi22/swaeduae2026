<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold leading-tight text-emerald-950">
            {{ __('Attendance') }} — {{ $event->titleForLocale() }}
        </h2>
    </x-slot>

    @php
        $cpLocaleQ = \App\Support\PublicLocale::query();
    @endphp
    <div class="pb-[max(2rem,env(safe-area-inset-bottom))] pt-6 sm:pt-8">
        <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
            <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50/90 p-4 text-sm text-slate-700" role="note">
                <p class="font-semibold text-slate-900">{{ __('Event summary') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-slate-600">
                    <li>{{ __('Event starts') }}: {{ $event->event_starts_at->locale(app()->getLocale())->isoFormat('LLL') }}</li>
                    <li>{{ __('Event ends') }}: {{ $event->event_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}</li>
                    <li>{{ __('Check-in window') }}: {{ $event->checkin_window_starts_at->locale(app()->getLocale())->isoFormat('LLL') }} – {{ $event->checkin_window_ends_at->locale(app()->getLocale())->isoFormat('LLL') }}</li>
                </ul>
                @if ($event->organization)
                    <p class="mt-2 text-xs text-slate-500">{{ $event->organization->nameForLocale() }}</p>
                @endif
            </div>

            <p class="mb-4 text-xs leading-relaxed text-slate-600">
                {{ __('Attendance checkpoint session hint') }}
            </p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert" aria-live="assertive">
                    <p class="font-semibold">{{ __('Could not record attendance') }}</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status" aria-live="polite">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert" aria-live="assertive">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm sm:rounded-lg">
                <div class="space-y-6 p-4 sm:p-6 text-slate-900" x-data="attendanceCheckpoint" x-init="requestLocation()">
                    <p class="text-base leading-relaxed text-slate-700">
                        {{ __('Allow location access to validate check-in against the event geofence.') }}
                    </p>

                    <div class="rounded-lg border border-slate-100 bg-slate-50/80 p-3 text-xs text-slate-600" aria-live="polite" data-testid="checkpoint-gps-status">
                        <template x-if="latitude !== null && ! locationError">
                            <p>
                                <span class="font-semibold text-slate-800">{{ __('GPS ready') }}</span>
                                <span class="mt-1 block font-mono text-[11px] text-slate-600" x-text="'Lat ' + Number(latitude).toFixed(5) + ', Lng ' + Number(longitude).toFixed(5) + (accuracy ? ', ±' + Math.round(accuracy) + ' m' : '')"></span>
                            </p>
                        </template>
                        <template x-if="latitude === null && ! locationError && locating">
                            <p class="font-medium text-slate-700">{{ __('Locating…') }}</p>
                        </template>
                        <template x-if="latitude === null && ! locationError && ! locating">
                            <p>{{ __('Tap refresh to capture your location before check-in or check-out.') }}</p>
                        </template>
                    </div>

                    <template x-if="locationError">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950" role="alert" aria-live="assertive" x-text="locationError"></div>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                                class="inline-flex min-h-[48px] min-w-[48px] touch-manipulation items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-emerald-800 disabled:opacity-50"
                                @click="requestLocation()"
                                x-bind:disabled="locating"
                                x-bind:aria-busy="locating"
                                aria-describedby="checkpoint-gps-help">
                            <span x-show="! locating">{{ __('Refresh GPS') }}</span>
                            <span x-show="locating">{{ __('Locating…') }}</span>
                        </button>
                    </div>
                    <p id="checkpoint-gps-help" class="sr-only">{{ __('Updates your coordinates for the buttons below.') }}</p>

                    <form method="post" action="{{ route('attendance.checkpoint.store', $event) }}" class="space-y-4" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="action" value="check_in">
                        <input type="hidden" name="latitude" x-bind:value="latitude ?? ''">
                        <input type="hidden" name="longitude" x-bind:value="longitude ?? ''">
                        <input type="hidden" name="accuracy_meters" x-bind:value="accuracy ?? ''">

                        <x-primary-button class="min-h-[52px] w-full justify-center text-base touch-manipulation" x-bind:disabled="latitude === null || locating || submitting">
                            {{ __('Check in') }}
                        </x-primary-button>
                    </form>

                    <form method="post" action="{{ route('attendance.checkpoint.store', $event) }}" class="space-y-4 border-t border-slate-100 pt-6" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="action" value="check_out">
                        <input type="hidden" name="latitude" x-bind:value="latitude ?? ''">
                        <input type="hidden" name="longitude" x-bind:value="longitude ?? ''">
                        <input type="hidden" name="accuracy_meters" x-bind:value="accuracy ?? ''">

                        <x-secondary-button class="min-h-[52px] w-full justify-center text-base touch-manipulation" type="submit" x-bind:disabled="latitude === null || locating || submitting">
                            {{ __('Check out') }}
                        </x-secondary-button>
                    </form>

                    @can('accessAttendanceCheckpoint', $event)
                        <div class="border-t border-slate-100 pt-4 text-center">
                            <a href="{{ route('volunteer.opportunities.show', array_merge(['event' => $event], $cpLocaleQ)) }}" class="text-sm font-semibold text-emerald-800 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-950">
                                {{ __('Back to opportunity') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('attendanceCheckpoint', () => ({
                latitude: null,
                longitude: null,
                accuracy: null,
                locating: false,
                submitting: false,
                locationError: null,
                messages: {
                    denied: @json(__('GPS permission was denied. Enable location for this site in your browser settings, then tap Refresh GPS.')),
                    unavailable: @json(__('GPS position could not be determined. Move outdoors or to a window, then try again.')),
                    timeout: @json(__('GPS timed out. Check signal, disable battery saver if needed, then tap Refresh GPS.')),
                    offline: @json(__('You appear to be offline. Connect to the internet, then refresh this page.')),
                    unsupported: @json(__('This device does not support location.')),
                    generic: @json(__('Location permission denied or unavailable.')),
                },
                requestLocation() {
                    this.locating = true;
                    this.locationError = null;
                    if (typeof navigator !== 'undefined' && navigator.onLine === false) {
                        this.locationError = this.messages.offline;
                        this.locating = false;
                        return;
                    }
                    if (! navigator.geolocation) {
                        this.locationError = this.messages.unsupported;
                        this.locating = false;
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.latitude = pos.coords.latitude;
                            this.longitude = pos.coords.longitude;
                            this.accuracy = pos.coords.accuracy;
                            this.locating = false;
                        },
                        (err) => {
                            if (err && err.code === 1) {
                                this.locationError = this.messages.denied;
                            } else if (err && err.code === 2) {
                                this.locationError = this.messages.unavailable;
                            } else if (err && err.code === 3) {
                                this.locationError = this.messages.timeout;
                            } else {
                                this.locationError = this.messages.generic;
                            }
                            this.locating = false;
                        },
                        { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }
                    );
                },
            }));
        });
    </script>
</x-app-layout>
