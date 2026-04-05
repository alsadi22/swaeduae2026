<?php

namespace App\Support;

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
}
