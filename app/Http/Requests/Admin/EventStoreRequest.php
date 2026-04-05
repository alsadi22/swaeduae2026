<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'application_required' => ['sometimes', 'boolean'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:1', 'max:50000'],
            'geofence_strict' => ['sometimes', 'boolean'],
            'min_gps_accuracy_meters' => ['nullable', 'integer', 'min:1', 'max:500'],
            'checkin_window_starts_at' => ['required', 'date'],
            'checkin_window_ends_at' => ['required', 'date', 'after:checkin_window_starts_at'],
            'event_starts_at' => ['required', 'date'],
            'event_ends_at' => ['required', 'date', 'after:event_starts_at'],
            'checkout_grace_minutes_after_event' => ['required', 'integer', 'min:0', 'max:1440'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'geofence_strict' => $this->boolean('geofence_strict'),
            'application_required' => $this->boolean('application_required'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $checkinStart = $this->date('checkin_window_starts_at');
            $checkinEnd = $this->date('checkin_window_ends_at');
            $eventStart = $this->date('event_starts_at');
            $eventEnd = $this->date('event_ends_at');

            if ($checkinEnd->lt($eventStart)) {
                $v->errors()->add(
                    'checkin_window_ends_at',
                    __('The check-in window must end on or after the event start time.')
                );
            }

            if ($checkinStart->gt($eventEnd)) {
                $v->errors()->add(
                    'checkin_window_starts_at',
                    __('The check-in window must start on or before the event end time.')
                );
            }
        });
    }
}
