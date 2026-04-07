<?php

namespace App\Http\Requests\Organization;

use App\Models\OrganizationDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrganizationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $org = $user?->organization;
        if ($org === null || ! $user->hasAnyRole(['org-owner', 'org-manager'])) {
            return false;
        }

        return $org->isPendingVerification() || $org->isApproved();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'max:5120',
                'mimes:pdf,jpeg,jpg,png',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $org = $user?->organization;
            if ($org === null) {
                return;
            }
            $count = $org->documents()->count();
            if ($count >= OrganizationDocument::MAX_FILES_PER_ORGANIZATION) {
                $validator->errors()->add(
                    'document',
                    __('Organization documents limit reached', ['max' => (string) OrganizationDocument::MAX_FILES_PER_ORGANIZATION])
                );
            }
        });
    }
}
