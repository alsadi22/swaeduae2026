<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AdminCmsOgImage
{
    private const STORAGE_PREFIX = '/storage/cms/og/';

    /**
     * Disk path (e.g. cms/og/12/file.jpg) when this URL/path is an uploaded CMS OG file we manage; otherwise null.
     */
    public static function managedDiskPath(?string $ogImage): ?string
    {
        if ($ogImage === null) {
            return null;
        }

        $trim = trim($ogImage);
        if ($trim === '' || ! Str::startsWith($trim, self::STORAGE_PREFIX)) {
            return null;
        }

        return Str::after($trim, '/storage/');
    }

    public static function deleteIfManaged(?string $ogImage): void
    {
        $path = self::managedDiskPath($ogImage);
        if ($path !== null && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
