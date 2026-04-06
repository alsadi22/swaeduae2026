<?php

namespace App\Support;

use App\Models\User;

final class PublicLocale
{
    /**
     * Query parameters to keep the active locale in shareable public URLs.
     *
     * @return array{lang: string}
     */
    public static function query(): array
    {
        return ['lang' => app()->getLocale()];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public static function mergeQuery(array $params): array
    {
        return array_merge(self::query(), $params);
    }

    /**
     * Use a user's saved preferred locale when present; otherwise fall back to the active app locale.
     *
     * @return array{lang: string}
     */
    public static function queryForUser(?User $user): array
    {
        if ($user !== null && is_string($user->locale_preferred) && in_array($user->locale_preferred, ['en', 'ar'], true)) {
            return ['lang' => $user->locale_preferred];
        }

        return self::query();
    }

    /**
     * Prefer explicit ?lang= on the current request so links match the page the user is viewing;
     * otherwise fall back to {@see self::queryForUser()}.
     *
     * @return array{lang: string}
     */
    public static function queryFromRequestOrUser(?User $user): array
    {
        $lang = request()->query('lang');
        if (is_string($lang) && in_array($lang, ['en', 'ar'], true)) {
            return ['lang' => $lang];
        }

        return self::queryForUser($user);
    }
}
