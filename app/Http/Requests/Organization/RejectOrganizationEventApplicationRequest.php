<?php

namespace App\Http\Requests\Organization;

use App\Models\EventApplication;
use Illuminate\Foundation\Http\FormRequest;

class RejectOrganizationEventApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EventApplication $application */
        $application = $this->route('event_application');

        return $this->user()->can('organizationPortalReview', $application);
    }

    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
