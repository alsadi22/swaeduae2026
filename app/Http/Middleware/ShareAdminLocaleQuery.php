<?php

namespace App\Http\Middleware;

use App\Support\PublicLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class ShareAdminLocaleQuery
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('adminLocaleQ', PublicLocale::query());

        return $next($request);
    }
}
