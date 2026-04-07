<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GalleryImageStoreRequest extends FormRequest
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
            'image' => ['required', 'file', 'max:5120', 'mimes:jpeg,jpg,png,webp,gif'],
            'alt_text_en' => ['nullable', 'string', 'max:255'],
            'alt_text_ar' => ['nullable', 'string', 'max:255'],
            'is_visible' => ['sometimes', 'boolean'],
        ];
    }
}
