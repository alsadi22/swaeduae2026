<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Mail\DisputeOpenedStaffMail;
use App\Models\Attendance;
use App\Models\Dispute;
use App\Services\Attendance\AttendanceJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VolunteerDisputeController extends Controller
{
    public function __construct(
        private readonly AttendanceJournal $journal,
    ) {}

    public function create(Request $request, Attendance $attendance): View
    {
        abort_unless($request->user()->hasRole('volunteer'), 403);

        $this->authorize('dispute', $attendance);

        $attendance->load('event.organization');

        return view('volunteer.attendance.dispute-create', compact('attendance'));
    }

    public function store(Request $request, Attendance $attendance): RedirectResponse
    {
        abort_unless($request->user()->hasRole('volunteer'), 403);

        $this->authorize('dispute', $attendance);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $dispute = Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $request->user()->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => $validated['description'],
        ]);

        $this->journal->append($attendance, 'dispute_opened', $request->user()->id, [
            'dispute_id' => $dispute->id,
        ]);

        $staffAddress = config('swaeduae.mail.staff_disputes');
        if (is_string($staffAddress) && $staffAddress !== '') {
            Mail::to($staffAddress)->queue(new DisputeOpenedStaffMail($dispute->fresh()));
        }

        return redirect()
            ->route('dashboard.attendance.index')
            ->with('status', __('Your dispute was submitted. An administrator will review it.'));
    }
}
