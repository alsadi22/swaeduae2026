@php
    $dt = function (string $attr) use ($event) {
        $v = old($attr);
        if ($v !== null) {
            return $v;
        }
        $m = $event->{$attr};
        return $m ? $m->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '';
    };
@endphp

@if ($errors->any())
    <div class="rounded-md bg-red-50 p-4 text-sm text-red-800 mb-6" role="alert">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-8">
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Basics') }}</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            @if ($organizationPortal ?? false)
                <div class="sm:col-span-2">
                    <x-input-label :value="__('Organization')" />
                    <p class="mt-1 text-sm text-gray-700">{{ $portalOrganization->nameForLocale() }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Organization portal event organization hint') }}</p>
                </div>
            @else
                <div class="sm:col-span-2">
                    <x-input-label for="organization_id" :value="__('Organization')" />
                    <select id="organization_id" name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">{{ __('Select organization') }}</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}" @selected((string) old('organization_id', $event->organization_id) === (string) $org->id)>{{ $org->name_en }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                    @if ($organizations->isEmpty())
                        <p class="mt-2 text-sm text-amber-800">
                            <a href="{{ route('admin.organizations.create') }}" class="underline font-medium">{{ __('Create an organization first') }}</a>
                        </p>
                    @endif
                </div>
            @endif
            <div class="sm:col-span-2">
                <x-input-label for="title_en" :value="__('Title (English)')" />
                <x-text-input id="title_en" name="title_en" type="text" class="mt-1 block w-full" :value="old('title_en', $event->title_en)" required />
                <x-input-error :messages="$errors->get('title_en')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="title_ar" :value="__('Title (Arabic)')" />
                <x-text-input id="title_ar" name="title_ar" type="text" class="mt-1 block w-full" :value="old('title_ar', $event->title_ar)" />
                <x-input-error :messages="$errors->get('title_ar')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="capacity" :value="__('Capacity')" />
                <x-text-input id="capacity" name="capacity" type="number" min="1" class="mt-1 block w-full" :value="old('capacity', $event->capacity)" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Leave blank for unlimited roster size.') }}</p>
                <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
            </div>
            <div class="sm:col-span-2 flex items-start gap-2">
                <input type="hidden" name="application_required" value="0" />
                <input id="application_required" name="application_required" type="checkbox" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('application_required', $event->application_required ? '1' : '0') === '1') />
                <div>
                    <x-input-label for="application_required" :value="__('Require application before joining')" class="!mb-0" />
                    <p class="mt-1 text-xs text-gray-500">{{ ($organizationPortal ?? false) ? __('Application required hint organization portal') : __('Volunteers must be approved by an admin before they can join the roster.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Location and check-in') }}</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="latitude" :value="__('Latitude')" />
                <x-text-input id="latitude" name="latitude" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('latitude', $event->latitude)" required />
                <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="longitude" :value="__('Longitude')" />
                <x-text-input id="longitude" name="longitude" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('longitude', $event->longitude)" required />
                <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="geofence_radius_meters" :value="__('Geofence radius (meters)')" />
                <x-text-input id="geofence_radius_meters" name="geofence_radius_meters" type="number" min="1" class="mt-1 block w-full" :value="old('geofence_radius_meters', $event->geofence_radius_meters ?? 100)" required />
                <x-input-error :messages="$errors->get('geofence_radius_meters')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="min_gps_accuracy_meters" :value="__('Min GPS accuracy (meters)')" />
                <x-text-input id="min_gps_accuracy_meters" name="min_gps_accuracy_meters" type="number" min="1" class="mt-1 block w-full" :value="old('min_gps_accuracy_meters', $event->min_gps_accuracy_meters)" />
                <x-input-error :messages="$errors->get('min_gps_accuracy_meters')" class="mt-2" />
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <input type="hidden" name="geofence_strict" value="0" />
                <input id="geofence_strict" name="geofence_strict" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('geofence_strict', $event->geofence_strict ? '1' : '0') === '1') />
                <x-input-label for="geofence_strict" :value="__('Strict geofence')" class="!mb-0" />
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Schedule') }}</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="checkin_window_starts_at" :value="__('Check-in window starts')" />
                <x-text-input id="checkin_window_starts_at" name="checkin_window_starts_at" type="datetime-local" class="mt-1 block w-full" :value="$dt('checkin_window_starts_at')" required />
                <x-input-error :messages="$errors->get('checkin_window_starts_at')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="checkin_window_ends_at" :value="__('Check-in window ends')" />
                <x-text-input id="checkin_window_ends_at" name="checkin_window_ends_at" type="datetime-local" class="mt-1 block w-full" :value="$dt('checkin_window_ends_at')" required />
                <x-input-error :messages="$errors->get('checkin_window_ends_at')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="event_starts_at" :value="__('Event starts')" />
                <x-text-input id="event_starts_at" name="event_starts_at" type="datetime-local" class="mt-1 block w-full" :value="$dt('event_starts_at')" required />
                <x-input-error :messages="$errors->get('event_starts_at')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="event_ends_at" :value="__('Event ends')" />
                <x-text-input id="event_ends_at" name="event_ends_at" type="datetime-local" class="mt-1 block w-full" :value="$dt('event_ends_at')" required />
                <x-input-error :messages="$errors->get('event_ends_at')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="checkout_grace_minutes_after_event" :value="__('Checkout grace (minutes)')" />
                <x-text-input id="checkout_grace_minutes_after_event" name="checkout_grace_minutes_after_event" type="number" min="0" max="1440" class="mt-1 block w-full" :value="old('checkout_grace_minutes_after_event', $event->checkout_grace_minutes_after_event ?? 30)" required />
                <x-input-error :messages="$errors->get('checkout_grace_minutes_after_event')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
