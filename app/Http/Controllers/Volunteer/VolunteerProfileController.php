<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Volunteer\VolunteerProfileUpdateRequest;
use App\Models\VolunteerProfile;
use App\Support\PublicLocale;
use App\Support\VolunteerProfileCompletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = VolunteerProfile::query()->firstWhere('user_id', $user->id)
            ?? new VolunteerProfile([
                'user_id' => $user->id,
                'notification_email_opt_in' => true,
            ]);

        return view('volunteer.profile.edit', [
            'profile' => $profile,
            'meetsMinimum' => $user->hasMinimumVolunteerProfileForCommitments(),
            'profileCompletionPercent' => VolunteerProfileCompletion::percent($profile),
        ]);
    }

    public function update(VolunteerProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $data['notification_email_opt_in'] = $request->boolean('notification_email_opt_in');

        $user->volunteerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return redirect()
            ->route('volunteer.profile.edit', PublicLocale::queryFromRequestOrUser($user))
            ->with('status', __('Volunteer profile updated.'));
    }
}
