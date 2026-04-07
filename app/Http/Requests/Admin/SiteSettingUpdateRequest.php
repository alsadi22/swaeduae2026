<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hero_mission_en' => ['nullable', 'string', 'max:5000'],
            'hero_mission_ar' => ['nullable', 'string', 'max:5000'],
            'hero_subline_en' => ['nullable', 'string', 'max:5000'],
            'hero_subline_ar' => ['nullable', 'string', 'max:5000'],
            'header_logo' => ['nullable', 'file', 'max:2048', 'mimes:jpeg,jpg,png,webp,gif,svg'],
            'remove_header_logo' => ['sometimes', 'boolean'],
        ];
    }
}
