<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\CheckinAttempt;
use App\Models\Dispute;
use App\Models\EventApplication;
use App\Models\ExternalNewsItem;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('org-roster-export', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(30)->by($id !== null ? 'org-roster-export|'.$id : 'org-roster-export|'.$request->ip());
        });

        RateLimiter::for('org-invitation-send', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(10)->by($id !== null ? 'org-invitation-send|'.$id : 'org-invitation-send|'.$request->ip());
        });

        RateLimiter::for('org-invitation-resend', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(10)->by($id !== null ? 'org-invitation-resend|'.$id : 'org-invitation-resend|'.$request->ip());
        });

        RateLimiter::for('guest-organization-register', function (Request $request): Limit {
            return Limit::perMinute(10)->by('guest-org-reg|'.$request->ip());
        });

        RateLimiter::for('guest-volunteer-register', function (Request $request): Limit {
            return Limit::perMinute(10)->by('guest-vol-reg|'.$request->ip());
        });

        RateLimiter::for('guest-login', function (Request $request): Limit {
            return Limit::perMinute(30)->by('guest-login|'.$request->ip());
        });

        RateLimiter::for('guest-password-email', function (Request $request): Limit {
            return Limit::perMinute(5)->by('guest-pwd-email|'.$request->ip());
        });

        RateLimiter::for('guest-contact-form', function (Request $request): Limit {
            return Limit::perMinute(5)->by('guest-contact-form|'.$request->ip());
        });

        RateLimiter::for('guest-support-form', function (Request $request): Limit {
            return Limit::perMinute(5)->by('guest-support-form|'.$request->ip());
        });

        RateLimiter::for('user-account-profile-update', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(10)->by($id !== null ? 'acct-profile|'.$id : 'acct-profile|guest');
        });

        RateLimiter::for('volunteer-profile-update', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(10)->by($id !== null ? 'vol-profile|'.$id : 'vol-profile|guest');
        });

        RateLimiter::for('user-password-update', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(10)->by($id !== null ? 'user-pwd|'.$id : 'user-pwd|guest');
        });

        RateLimiter::for('email-verification-notification', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(6)->by($id !== null ? 'email-verify-send|'.$id : 'email-verify-send|guest');
        });

        RateLimiter::for('email-verification-verify', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(6)->by($id !== null ? 'email-verify-link|'.$id : 'email-verify-link|'.$request->ip());
        });

        RateLimiter::for('attendance-checkpoint-store', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(30)->by($id !== null ? 'checkpoint-post|'.$id : 'checkpoint-post|guest');
        });

        RateLimiter::for('user-data-export', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perHour(3)->by($id !== null ? 'user-data-export|'.$id : 'user-data-export|'.$request->ip());
        });

        RateLimiter::for('org-verification-resubmit', function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();

            return Limit::perHour(5)->by($id !== null ? 'org-verification-resubmit|'.$id : 'org-verification-resubmit|'.$request->ip());
        });

        Gate::define('view-organization-event-applications', function (User $user): bool {
            if ($user->organization_id === null || ! $user->organization?->isApproved()) {
                return false;
            }

            return $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator', 'org-viewer']);
        });

        Gate::define('view-organization-events', function (User $user): bool {
            if ($user->organization_id === null || ! $user->organization?->isApproved()) {
                return false;
            }

            return $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator', 'org-viewer']);
        });

        Gate::define('manage-organization-events', function (User $user): bool {
            if ($user->organization_id === null || ! $user->organization?->isApproved()) {
                return false;
            }

            return $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator']);
        });

        Gate::define('configure-organization-events', function (User $user): bool {
            if ($user->organization_id === null || ! $user->organization?->isApproved()) {
                return false;
            }

            return $user->hasAnyRole(['org-owner', 'org-manager']);
        });

        Route::bind('attendance', function (string $value) {
            if (! auth()->check()) {
                abort(404);
            }

            $query = Attendance::query()->whereKey($value);

            $user = auth()->user();
            $isAdmin = $user && $user->hasAnyRole(['admin', 'super-admin']);
            if (request()->is('admin/*') && $isAdmin) {
                return $query->firstOrFail();
            }

            return $query->where('user_id', auth()->id())->firstOrFail();
        });

        $replyTo = config('mail.reply_to.address');

        if (is_string($replyTo) && $replyTo !== '') {
            Mail::alwaysReplyTo($replyTo, config('mail.reply_to.name'));
        }

        View::composer('layouts.navigation', function (\Illuminate\View\View $view): void {
            $count = 0;
            $pendingOrgs = 0;
            $openDisputesCount = 0;
            $flaggedAttendanceRowsCount = 0;
            $suspiciousCheckinAttemptsRecentCount = 0;
            $pendingOrgEventApplicationsCount = 0;
            $pendingExternalNewsItemsCount = 0;
            $user = Auth::user();
            if ($user && $user->hasAnyRole(['admin', 'super-admin'])) {
                $count = EventApplication::query()
                    ->where('status', EventApplication::STATUS_PENDING)
                    ->count();
                $pendingOrgs = Organization::query()->pendingVerification()->count();
                $openDisputesCount = Dispute::query()
                    ->where('status', Dispute::STATUS_OPEN)
                    ->count();
                $flaggedAttendanceRowsCount = Attendance::query()->withNonEmptySuspicionFlags()->count();
                $suspiciousCheckinAttemptsRecentCount = CheckinAttempt::query()
                    ->where('outcome', 'suspicious')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
                $pendingExternalNewsItemsCount = ExternalNewsItem::query()
                    ->where('status', ExternalNewsItem::STATUS_PENDING_REVIEW)
                    ->count();
            }
            if ($user && Gate::forUser($user)->allows('view-organization-event-applications')) {
                $pendingOrgEventApplicationsCount = EventApplication::query()
                    ->where('status', EventApplication::STATUS_PENDING)
                    ->whereHas('event', fn ($q) => $q->where('organization_id', $user->organization_id))
                    ->count();
            }
            $view->with('pendingEventApplicationsCount', $count);
            $view->with('pendingOrganizationVerificationsCount', $pendingOrgs);
            $view->with('openDisputesCount', $openDisputesCount);
            $view->with('flaggedAttendanceRowsCount', $flaggedAttendanceRowsCount);
            $view->with('suspiciousCheckinAttemptsRecentCount', $suspiciousCheckinAttemptsRecentCount);
            $view->with('pendingOrganizationEventApplicationsCount', $pendingOrgEventApplicationsCount);
            $view->with('pendingExternalNewsItemsCount', $pendingExternalNewsItemsCount);
        });
    }
}
