<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    private function managesSite(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, SiteSetting $siteSetting): bool
    {
        return $this->managesSite($user);
    }

    public function update(User $user, SiteSetting $siteSetting): bool
    {
        return $this->managesSite($user);
    }
}
