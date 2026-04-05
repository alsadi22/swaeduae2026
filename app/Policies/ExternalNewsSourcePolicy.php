<?php

namespace App\Policies;

use App\Models\ExternalNewsSource;
use App\Models\User;

class ExternalNewsSourcePolicy
{
    private function managesContent(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->managesContent($user);
    }

    public function view(User $user, ExternalNewsSource $externalNewsSource): bool
    {
        return $this->managesContent($user);
    }

    public function create(User $user): bool
    {
        return $this->managesContent($user);
    }

    public function update(User $user, ExternalNewsSource $externalNewsSource): bool
    {
        return $this->managesContent($user);
    }

    public function delete(User $user, ExternalNewsSource $externalNewsSource): bool
    {
        return $this->managesContent($user);
    }
}
