<?php

namespace App\Http\Requests\Volunteer;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class ApplyToOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()->can('applyToEvent', $event);
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
