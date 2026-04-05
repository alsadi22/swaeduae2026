<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitRejectedOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $organization = $user?->organization;

        return $user !== null
            && $user->hasRole('org-owner')
            && $organization !== null
            && $organization->isRejected();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ];
    }
}
