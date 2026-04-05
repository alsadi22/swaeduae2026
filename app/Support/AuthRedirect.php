<?php

namespace App\Support;

use App\Models\User;

class AuthRedirect
{
    /**
     * Default post-auth landing URL for the given user (intended URL overrides when used with redirect()->intended()).
     */
    public static function homeForUser(User $user, bool $withVerifiedQuery = false): string
    {
        $path = match (true) {
            $user->hasAnyRole(['super-admin', 'admin']) => route('admin.cms-pages.index', absolute: false),
            $user->hasAnyRole(['org-owner', 'org-manager', 'org-coordinator', 'org-viewer']) => route('organization.dashboard', absolute: false),
            default => route('dashboard', absolute: false),
        };

        $queries = PublicLocale::query();
        if (is_string($user->locale_preferred) && in_array($user->locale_preferred, ['en', 'ar'], true)) {
            $queries['lang'] = $user->locale_preferred;
        }
        if ($withVerifiedQuery) {
            $queries['verified'] = '1';
        }

        $sep = str_contains($path, '?') ? '&' : '?';

        return $path.$sep.http_build_query($queries);
    }
}
