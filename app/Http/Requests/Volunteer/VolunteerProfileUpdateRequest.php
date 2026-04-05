<?php

namespace App\Http\Requests\Volunteer;

use Illuminate\Foundation\Http\FormRequest;

class VolunteerProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('volunteer') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bio' => ['required', 'string', 'min:20', 'max:5000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'availability' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:64'],
            'emirates_id_masked' => ['nullable', 'string', 'max:32'],
            'notification_email_opt_in' => ['sometimes', 'boolean'],
        ];
    }
}
