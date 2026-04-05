<?php

namespace App\Policies;

use App\Models\ExternalNewsItem;
use App\Models\User;

class ExternalNewsItemPolicy
{
    private function managesContent(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->managesContent($user);
    }

    public function view(User $user, ExternalNewsItem $externalNewsItem): bool
    {
        return $this->managesContent($user);
    }

    public function update(User $user, ExternalNewsItem $externalNewsItem): bool
    {
        return $this->managesContent($user);
    }
}
