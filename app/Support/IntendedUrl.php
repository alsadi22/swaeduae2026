<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Stores a safe internal URL in session as Laravel's url.intended (used by redirect()->intended()).
 */
final class IntendedUrl
{
    private const MAX_LENGTH = 2048;

    /**
     * If the request has a valid ?return= value, set session url.intended.
     */
    public static function captureFromQuery(Request $request, string $key = 'return'): void
    {
        $url = self::sanitize($request->query($key));
        if ($url !== null) {
            session()->put('url.intended', $url);
        }
    }

    /**
     * @return array<string, string> Route parameters for ?return=
     */
    public static function queryParamsForRequestUri(Request $request): array
    {
        $uri = $request->getRequestUri();

        return self::queryParamsForRelativeUri($uri);
    }

    /**
     * @return array<string, string>
     */
    public static function queryParamsForRelativeUri(string $uri): array
    {
        $uri = trim($uri);
        if ($uri === '' || strlen($uri) > self::MAX_LENGTH) {
            return [];
        }
        if (! str_starts_with($uri, '/') || str_starts_with($uri, '//')) {
            return [];
        }

        return ['return' => $uri];
    }

    /**
     * Accept same-app path (/foo) or full URL matching APP_URL host. Rejects open redirects.
     */
    public static function sanitize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $s = trim($value);
        if ($s === '' || strlen($s) > self::MAX_LENGTH) {
            return null;
        }

        if (str_starts_with($s, '/') && ! str_starts_with($s, '//')) {
            return url($s);
        }

        $parsed = parse_url($s);
        if ($parsed === false || empty($parsed['scheme']) || empty($parsed['host'])) {
            return null;
        }

        if (! in_array(strtolower((string) $parsed['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if ($appHost === null || $appHost === '' || strcasecmp((string) $parsed['host'], $appHost) !== 0) {
            return null;
        }

        return $s;
    }
}
