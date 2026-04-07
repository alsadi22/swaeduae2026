<?php

use App\Http\Controllers\Admin\AdminTwoFactorChallengeController;
use App\Http\Controllers\Admin\AdminTwoFactorSetupController;
use App\Http\Controllers\Admin\AttendanceMinutesAdjustmentController;
use App\Http\Controllers\Admin\CheckinAttemptController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\EventApplicationController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ExternalNewsItemController;
use App\Http\Controllers\Admin\ExternalNewsSourceController;
use App\Http\Controllers\Admin\FlaggedAttendanceController;
use App\Http\Controllers\Admin\GalleryImageController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\OrganizationDocumentDownloadController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RequireAdminTwoFactor;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::get('two-factor-challenge', [AdminTwoFactorChallengeController::class, 'create'])
        ->name('admin.two-factor.challenge');
    Route::post('two-factor-challenge', [AdminTwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:admin-two-factor-challenge')
        ->name('admin.two-factor.challenge.store');
});

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])->group(function () {
    Route::get('two-factor/setup', [AdminTwoFactorSetupController::class, 'show'])->name('admin.two-factor.setup');
    Route::post('two-factor/setup', [AdminTwoFactorSetupController::class, 'store'])
        ->middleware('throttle:admin-two-factor-challenge')
        ->name('admin.two-factor.setup.store');
});

Route::middleware(['auth', 'verified', 'role:admin|super-admin', RequireAdminTwoFactor::class])
    ->name('admin.')
    ->group(function () {
        Route::get('/', function (Request $request): RedirectResponse {
            return redirect()->route(
                'admin.cms-pages.index',
                PublicLocale::queryFromRequestOrUser($request->user()),
                302
            );
        })->name('dashboard');
        Route::get('cms-pages/export', [CmsPageController::class, 'exportCsv'])
            ->middleware('throttle:admin-cms-pages-export')
            ->name('cms-pages.export');
        Route::get('cms-pages/{cms_page}/preview', [CmsPageController::class, 'preview'])
            ->name('cms-pages.preview');
        Route::resource('cms-pages', CmsPageController::class)->except(['show']);
        Route::get('site-settings/edit', [SiteSettingController::class, 'edit'])
            ->name('site-settings.edit');
        Route::put('site-settings', [SiteSettingController::class, 'update'])
            ->middleware('throttle:admin-site-settings-update')
            ->name('site-settings.update');
        Route::post('gallery-images', [GalleryImageController::class, 'store'])
            ->middleware('throttle:admin-gallery-image-upload')
            ->name('gallery-images.store');
        Route::put('gallery-images/{gallery_image}', [GalleryImageController::class, 'update'])
            ->middleware('throttle:admin-gallery-image-upload')
            ->name('gallery-images.update');
        Route::resource('gallery-images', GalleryImageController::class)->except(['show', 'store', 'update']);
        Route::get('external-news-sources/export', [ExternalNewsSourceController::class, 'exportCsv'])
            ->middleware('throttle:admin-external-news-sources-export')
            ->name('external-news-sources.export');
        Route::resource('external-news-sources', ExternalNewsSourceController::class)->except(['show']);
        Route::post('external-news-sources/{external_news_source}/fetch', [ExternalNewsSourceController::class, 'fetch'])
            ->name('external-news-sources.fetch');
        Route::get('external-news-sources/{external_news_source}/logs', [ExternalNewsSourceController::class, 'logs'])
            ->name('external-news-sources.logs');
        Route::get('external-news-items/export', [ExternalNewsItemController::class, 'exportCsv'])
            ->middleware('throttle:admin-external-news-items-export')
            ->name('external-news-items.export');
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
        Route::get('events/export', [EventController::class, 'exportCsv'])
            ->middleware('throttle:admin-events-export')
            ->name('events.export');
        Route::resource('events', EventController::class)->except(['show']);
        Route::post('events/{event}/checkpoint-signed-url', [EventController::class, 'checkpointSignedUrl'])
            ->name('events.checkpoint-signed-url');
        Route::post('organizations/{organization}/approve', [OrganizationController::class, 'approve'])
            ->name('organizations.approve');
        Route::post('organizations/{organization}/reject', [OrganizationController::class, 'reject'])
            ->name('organizations.reject');
        Route::get('organizations/export', [OrganizationController::class, 'exportCsv'])
            ->middleware('throttle:admin-organizations-export')
            ->name('organizations.export');
        Route::resource('organizations', OrganizationController::class)->except(['show']);
        Route::get('organizations/{organization}/documents/{organization_document}/download', OrganizationDocumentDownloadController::class)
            ->name('organizations.documents.download');
        Route::get('event-applications/export', [EventApplicationController::class, 'exportCsv'])
            ->middleware('throttle:admin-event-applications-export')
            ->name('event-applications.export');
        Route::get('event-applications', [EventApplicationController::class, 'index'])->name('event-applications.index');
        Route::post('event-applications/{event_application}/approve', [EventApplicationController::class, 'approve'])
            ->name('event-applications.approve');
        Route::post('event-applications/{event_application}/reject', [EventApplicationController::class, 'reject'])
            ->name('event-applications.reject');
        Route::get('disputes/export', [DisputeController::class, 'exportCsv'])
            ->middleware('throttle:admin-disputes-export')
            ->name('disputes.export');
        Route::get('disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::get('disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
        Route::post('disputes/{dispute}/resolve', [DisputeController::class, 'resolve'])->name('disputes.resolve');
        Route::get('checkin-attempts/export', [CheckinAttemptController::class, 'exportCsv'])
            ->middleware('throttle:admin-checkin-attempts-export')
            ->name('checkin-attempts.export');
        Route::get('checkin-attempts', [CheckinAttemptController::class, 'index'])->name('checkin-attempts.index');
        Route::get('flagged-attendance/export', [FlaggedAttendanceController::class, 'exportCsv'])
            ->middleware('throttle:admin-flagged-attendance-export')
            ->name('flagged-attendance.export');
        Route::get('flagged-attendance', [FlaggedAttendanceController::class, 'index'])->name('flagged-attendance.index');
        Route::post('attendances/{attendance}/minutes-adjustment', [AttendanceMinutesAdjustmentController::class, 'update'])
            ->name('attendances.minutes-adjustment.update');
    });
