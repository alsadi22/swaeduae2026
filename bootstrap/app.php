<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShareAdminLocaleQuery;
use App\Support\AuthRedirect;
use App\Support\PublicLocale;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/public.php',
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/attendance.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', ShareAdminLocaleQuery::class])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login', absolute: false);
            }

            return route('login', PublicLocale::query(), absolute: false);
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user === null) {
                return route('home', PublicLocale::query(), absolute: false);
            }

            return AuthRedirect::homeForUser($user);
        });

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);

        // Do not use config() here — the config repository is not bound yet during bootstrap.
        $trustProxies = filter_var(env('TRUSTED_PROXIES', false), FILTER_VALIDATE_BOOLEAN);
        if ($trustProxies) {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('swaeduae:fetch-external-news')->hourly();
    })
    ->create();
