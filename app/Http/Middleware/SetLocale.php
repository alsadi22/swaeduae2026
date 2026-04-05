<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const LOCALES = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if (is_string($locale) && in_array($locale, self::LOCALES, true)) {
            session(['locale' => $locale]);
        }

        $locale = session('locale', config('app.locale'));

        if (! is_string($locale) || ! in_array($locale, self::LOCALES, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
