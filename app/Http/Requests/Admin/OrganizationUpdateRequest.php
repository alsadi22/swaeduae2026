<?php

namespace App\Http\Requests\Admin;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return $this->user()->can('update', $organization);
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ];
    }
}
