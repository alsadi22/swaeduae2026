<?php

namespace App\Policies;

use App\Models\CmsPage;
use App\Models\User;

class CmsPagePolicy
{
    private function managesContent(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->managesContent($user);
    }

    public function view(User $user, CmsPage $cmsPage): bool
    {
        return $this->managesContent($user);
    }

    public function create(User $user): bool
    {
        return $this->managesContent($user);
    }

    public function update(User $user, CmsPage $cmsPage): bool
    {
        return $this->managesContent($user);
    }

    public function delete(User $user, CmsPage $cmsPage): bool
    {
        return $this->managesContent($user);
    }
}
