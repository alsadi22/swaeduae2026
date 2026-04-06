<?php

namespace App\Support;

use Illuminate\Support\Str;

final class PublicBreadcrumbs
{
    /**
     * @return list<array{name: string, url: string}>
     */
    public static function homeAndCurrent(string $currentName, string $currentAbsoluteUrl): array
    {
        $q = PublicLocale::queryFromRequestOrUser(auth()->user());

        return [
            ['name' => __('Home'), 'url' => route('home', $q, true)],
            ['name' => $currentName, 'url' => $currentAbsoluteUrl],
        ];
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    public static function homeMediaAndExternalItem(string $itemTitle, string $itemAbsoluteUrl): array
    {
        $q = PublicLocale::queryFromRequestOrUser(auth()->user());

        return [
            ['name' => __('Home'), 'url' => route('home', $q, true)],
            ['name' => __('Media center'), 'url' => route('media.index', $q, true)],
            ['name' => Str::limit($itemTitle, 72), 'url' => $itemAbsoluteUrl],
        ];
    }
}
