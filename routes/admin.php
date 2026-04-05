<?php

use App\Http\Controllers\Admin\AttendanceMinutesAdjustmentController;
use App\Http\Controllers\Admin\CheckinAttemptController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\ExternalNewsItemController;
use App\Http\Controllers\Admin\ExternalNewsSourceController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\EventApplicationController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FlaggedAttendanceController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
});

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('cms-pages/{cms_page}/preview', [CmsPageController::class, 'preview'])
            ->name('cms-pages.preview');
        Route::resource('cms-pages', CmsPageController::class)->except(['show']);
        Route::resource('external-news-sources', ExternalNewsSourceController::class)->except(['show']);
        Route::post('external-news-sources/{external_news_source}/fetch', [ExternalNewsSourceController::class, 'fetch'])
            ->name('external-news-sources.fetch');
        Route::get('external-news-sources/{external_news_source}/logs', [ExternalNewsSourceController::class, 'logs'])
            ->name('external-news-sources.logs');
        Route::get('external-news-items', [ExternalNewsItemController::class, 'index'])->name('external-news-items.index');
        Route::get('external-news-items/{external_news_item}/edit', [ExternalNewsItemController::class, 'edit'])
            ->name('external-news-items.edit');
        Route::put('external-news-items/{external_news_item}', [ExternalNewsItemController::class, 'update'])
            ->name('external-news-items.update');
        Route::post('external-news-items/bulk', [ExternalNewsItemController::class, 'bulk'])->name('external-news-items.bulk');
        Route::post('external-news-items/{external_news_item}/approve', [ExternalNewsItemController::class, 'approve'])
            ->name('external-news-items.approve');
        Route::post('external-news-items/{external_news_item}/publish', [ExternalNewsItemController::class, 'publish'])
            ->name('external-news-items.publish');
        Route::post('external-news-items/{external_news_item}/reject', [ExternalNewsItemController::class, 'reject'])
            ->name('external-news-items.reject');
        Route::post('external-news-items/{external_news_item}/unpublish', [ExternalNewsItemController::class, 'unpublish'])
            ->name('external-news-items.unpublish');
        Route::resource('events', EventController::class)->except(['show']);
        Route::post('events/{event}/checkpoint-signed-url', [EventController::class, 'checkpointSignedUrl'])
            ->name('events.checkpoint-signed-url');
        Route::post('organizations/{organization}/approve', [OrganizationController::class, 'approve'])
            ->name('organizations.approve');
        Route::post('organizations/{organization}/reject', [OrganizationController::class, 'reject'])
            ->name('organizations.reject');
        Route::resource('organizations', OrganizationController::class)->except(['show']);
        Route::get('event-applications', [EventApplicationController::class, 'index'])->name('event-applications.index');
        Route::post('event-applications/{event_application}/approve', [EventApplicationController::class, 'approve'])
            ->name('event-applications.approve');
        Route::post('event-applications/{event_application}/reject', [EventApplicationController::class, 'reject'])
            ->name('event-applications.reject');
        Route::get('disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::get('disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
        Route::post('disputes/{dispute}/resolve', [DisputeController::class, 'resolve'])->name('disputes.resolve');
        Route::get('checkin-attempts', [CheckinAttemptController::class, 'index'])->name('checkin-attempts.index');
        Route::get('flagged-attendance', [FlaggedAttendanceController::class, 'index'])->name('flagged-attendance.index');
        Route::post('attendances/{attendance}/minutes-adjustment', [AttendanceMinutesAdjustmentController::class, 'update'])
            ->name('attendances.minutes-adjustment.update');
    });
