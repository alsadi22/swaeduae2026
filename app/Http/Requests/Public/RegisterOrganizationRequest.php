<?php

namespace App\Http\Requests\Public;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'locale_preferred' => ['required', 'string', 'in:en,ar'],
            'terms' => ['accepted'],
        ];
    }
}
