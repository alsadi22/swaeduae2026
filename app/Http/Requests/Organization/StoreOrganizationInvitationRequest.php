<?php

namespace App\Http\Requests\Organization;

use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OrganizationInvitation::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(OrganizationInvitation::INVITABLE_ROLES)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $organization = $user->organization;
            if ($organization === null) {
                return;
            }

            $email = strtolower((string) $this->input('email'));

            if (strtolower($user->email) === $email) {
                $validator->errors()->add('email', __('You cannot invite your own account email.'));

                return;
            }

            if (User::query()->where('organization_id', $organization->id)->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                $validator->errors()->add('email', __('This email already belongs to a member of your organization.'));
            }
        });
    }
}
