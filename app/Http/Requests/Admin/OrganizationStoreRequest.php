<?php

namespace App\Http\Requests\Admin;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Organization::class);
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ];
    }
}
