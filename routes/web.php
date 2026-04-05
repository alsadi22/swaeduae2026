<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Organization\OrganizationEventApplicationController;
use App\Http\Controllers\Organization\OrganizationEventController;
use App\Http\Controllers\Organization\OrganizationInvitationController;
use App\Http\Controllers\Organization\OrganizationVerificationResubmitController;
use App\Http\Controllers\OrganizationDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDataExportController;
use App\Http\Controllers\Volunteer\VolunteerAttendanceController;
use App\Http\Controllers\Volunteer\VolunteerDisputeController;
use App\Http\Controllers\Volunteer\VolunteerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/organization/dashboard', OrganizationDashboardController::class)
    ->middleware(['auth', 'verified', 'role:org-owner|org-manager|org-coordinator|org-viewer'])
    ->name('organization.dashboard');

Route::middleware(['auth', 'verified', 'can:view-organization-events'])->group(function () {
    Route::get('/organization/events', [OrganizationEventController::class, 'index'])
        ->name('organization.events.index');
    Route::get('/organization/events/{event}/roster', [OrganizationEventController::class, 'roster'])
        ->name('organization.events.roster');
    Route::get('/organization/events/{event}/roster/export', [OrganizationEventController::class, 'exportRoster'])
        ->middleware('throttle:org-roster-export')
        ->name('organization.events.roster.export');
});

Route::middleware(['auth', 'verified', 'can:configure-organization-events'])->group(function () {
    Route::get('/organization/events/create', [OrganizationEventController::class, 'create'])
        ->name('organization.events.create');
    Route::post('/organization/events', [OrganizationEventController::class, 'store'])
        ->name('organization.events.store');
    Route::get('/organization/events/{event}/edit', [OrganizationEventController::class, 'edit'])
        ->name('organization.events.edit');
    Route::put('/organization/events/{event}', [OrganizationEventController::class, 'update'])
        ->name('organization.events.update');
    Route::delete('/organization/events/{event}', [OrganizationEventController::class, 'destroy'])
        ->name('organization.events.destroy');
    Route::post('/organization/events/{event}/checkpoint-signed-url', [OrganizationEventController::class, 'checkpointSignedUrl'])
        ->name('organization.events.checkpoint-signed-url');
});

Route::middleware(['auth', 'verified', 'can:manage-organization-events'])->group(function () {
    Route::delete('/organization/events/{event}/roster/{volunteer}', [OrganizationEventController::class, 'destroyRosterVolunteer'])
        ->name('organization.events.roster.volunteers.destroy');
});

Route::middleware(['auth', 'verified', 'can:view-organization-event-applications'])->group(function () {
    Route::get('/organization/event-applications', [OrganizationEventApplicationController::class, 'index'])
        ->name('organization.event-applications.index');
    Route::post('/organization/event-applications/{event_application}/approve', [OrganizationEventApplicationController::class, 'approve'])
        ->name('organization.event-applications.approve');
    Route::post('/organization/event-applications/{event_application}/reject', [OrganizationEventApplicationController::class, 'reject'])
        ->name('organization.event-applications.reject');
});

Route::middleware(['auth', 'verified', 'role:org-owner', 'throttle:org-verification-resubmit'])->group(function () {
    Route::post('/organization/verification-resubmit', OrganizationVerificationResubmitController::class)
        ->name('organization.verification-resubmit');
});

Route::middleware(['auth', 'verified', 'role:org-owner'])->group(function () {
    Route::post('/organization/invitations', [OrganizationInvitationController::class, 'store'])
        ->middleware('throttle:org-invitation-send')
        ->name('organization.invitations.store');
    Route::post('/organization/invitations/{invitation}/resend', [OrganizationInvitationController::class, 'resend'])
        ->middleware('throttle:org-invitation-resend')
        ->name('organization.invitations.resend');
    Route::delete('/organization/invitations/{invitation}', [OrganizationInvitationController::class, 'destroy'])
        ->name('organization.invitations.destroy');
});

Route::middleware(['auth', 'verified', 'role:volunteer'])->group(function () {
    Route::get('/volunteer/profile', [VolunteerProfileController::class, 'edit'])
        ->name('volunteer.profile.edit');
    Route::patch('/volunteer/profile', [VolunteerProfileController::class, 'update'])
        ->middleware('throttle:volunteer-profile-update')
        ->name('volunteer.profile.update');

    Route::get('/dashboard/attendance', [VolunteerAttendanceController::class, 'index'])
        ->name('dashboard.attendance.index');
    Route::get('/dashboard/attendance/{attendance}/dispute', [VolunteerDisputeController::class, 'create'])
        ->name('dashboard.attendance.disputes.create');
    Route::post('/dashboard/attendance/{attendance}/disputes', [VolunteerDisputeController::class, 'store'])
        ->name('dashboard.attendance.disputes.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:user-account-profile-update')
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'throttle:user-data-export'])->group(function () {
    Route::get('/profile/data-export', UserDataExportController::class)->name('profile.data-export');
});

require __DIR__.'/auth.php';
