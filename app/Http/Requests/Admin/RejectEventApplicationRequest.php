<?php

namespace App\Http\Requests\Admin;

use App\Models\EventApplication;
use Illuminate\Foundation\Http\FormRequest;

class RejectEventApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EventApplication $application */
        $application = $this->route('event_application');

        return $this->user()->can('review', $application);
    }

    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
