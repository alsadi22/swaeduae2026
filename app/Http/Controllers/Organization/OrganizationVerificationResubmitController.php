<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\ResubmitRejectedOrganizationRequest;
use App\Models\Organization;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;

class OrganizationVerificationResubmitController extends Controller
{
    public function __invoke(ResubmitRejectedOrganizationRequest $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(404);
        }

        $data = $request->validated();
        $organization->update([
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'] ?? null,
            'verification_status' => Organization::VERIFICATION_PENDING,
            'verification_review_note' => null,
            'verification_reviewed_at' => null,
        ]);

        return redirect()
            ->to(AuthRedirect::homeForUser($request->user()))
            ->with('status', __('Organization verification resubmitted.'));
    }
}
