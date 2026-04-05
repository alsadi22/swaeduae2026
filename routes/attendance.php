<?php

use App\Http\Controllers\Attendance\AttendanceCheckpointController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'signed', 'auth'])->group(function () {
    Route::get('/attendance/checkpoint/{event:uuid}', [AttendanceCheckpointController::class, 'show'])
        ->name('attendance.checkpoint.show');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/attendance/checkpoint/{event:uuid}', [AttendanceCheckpointController::class, 'store'])
        ->middleware('throttle:attendance-checkpoint-store')
        ->name('attendance.checkpoint.store');
});
