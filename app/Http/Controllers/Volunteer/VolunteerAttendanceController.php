<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VolunteerAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole('volunteer'), 403);

        $validated = $request->validate([
            'state' => [
                'nullable',
                'string',
                Rule::in([
                    'all',
                    Attendance::STATE_PENDING,
                    Attendance::STATE_CHECKED_IN,
                    Attendance::STATE_CHECKED_OUT,
                    Attendance::STATE_NO_SHOW,
                    Attendance::STATE_INCOMPLETE,
                ]),
            ],
        ]);
        $stateFilter = $validated['state'] ?? 'all';

        $query = $user
            ->attendances()
            ->with(['event.organization', 'disputes' => fn ($q) => $q->orderByDesc('created_at')])
            ->orderByDesc('updated_at');

        if ($stateFilter !== 'all') {
            $query->where('state', $stateFilter);
        }

        $attendances = $query->paginate(20)->withQueryString()->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('volunteer.attendance.index', compact('attendances', 'stateFilter'));
    }
}
