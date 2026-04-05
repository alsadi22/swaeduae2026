<?php

namespace App\Http\Requests\Admin;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return $this->user()->can('reject', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
