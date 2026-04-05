<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Attendance\AttendanceCheckpointService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCheckpointController extends Controller
{
    public function show(Request $request, Event $event): View
    {
        $request->session()->put('attendance_gate', [
            'event_id' => $event->id,
            'expires_at' => now()->addMinutes(20)->toIso8601String(),
        ]);

        return view('attendance.checkpoint', [
            'event' => $event->loadMissing('organization'),
        ]);
    }

    public function store(Request $request, Event $event, AttendanceCheckpointService $service): RedirectResponse|JsonResponse
    {
        $gate = $request->session()->get('attendance_gate');

        if (! is_array($gate) || ($gate['event_id'] ?? null) !== $event->id) {
            abort(403, __('Open the check-in link from the QR code again.'));
        }

        if (! isset($gate['expires_at']) || now()->isAfter(Carbon::parse($gate['expires_at']))) {
            abort(403, __('This check-in link expired. Scan the QR again.'));
        }

        $validated = $request->validate([
            'action' => 'required|in:check_in,check_out',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy_meters' => 'nullable|numeric|min:0|max:50000',
        ]);

        $result = $service->process(
            $request->user(),
            $event,
            $validated['action'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy_meters']) ? (float) $validated['accuracy_meters'] : null,
            $request->ip() ?? '0.0.0.0',
        );

        if ($request->wantsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        if ($result['ok']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message'])->withInput();
    }
}
