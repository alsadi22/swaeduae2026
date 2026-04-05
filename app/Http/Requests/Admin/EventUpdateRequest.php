<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EventUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        return (new EventStoreRequest)->rules();
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
