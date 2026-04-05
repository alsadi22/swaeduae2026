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

        return $withVerifiedQuery ? $path.'?verified=1' : $path;
    }
}
