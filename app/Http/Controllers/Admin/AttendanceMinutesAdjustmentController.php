<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\Attendance\AttendanceJournal;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceMinutesAdjustmentController extends Controller
{
    public function __construct(
        private readonly AttendanceJournal $journal,
    ) {}

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('adjustMinutes', $attendance);

        $validated = $request->validate([
            'minutes_adjustment' => ['nullable', 'integer', 'min:-10080', 'max:10080'],
            'minutes_adjustment_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousAdjustment = $attendance->minutes_adjustment;
        $previousVerified = $attendance->verifiedMinutes();

        $adjustment = $validated['minutes_adjustment'] ?? null;
        $note = $adjustment === null ? null : ($validated['minutes_adjustment_note'] ?? null);

        $attendance->update([
            'minutes_adjustment' => $adjustment,
            'minutes_adjustment_note' => $note,
        ]);
        $attendance->refresh();

        $this->journal->append($attendance, 'minutes_adjustment', $request->user()->id, [
            'previous_adjustment' => $previousAdjustment,
            'new_adjustment' => $attendance->minutes_adjustment,
            'previous_verified_minutes' => $previousVerified,
            'new_verified_minutes' => $attendance->verifiedMinutes(),
            'had_note' => filled($note),
        ]);

        return redirect()
            ->route('admin.flagged-attendance.index', PublicLocale::query())
            ->with('status', __('Attendance minutes adjustment saved.'));
    }
}
