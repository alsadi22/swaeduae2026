<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
{
    private function managesGallery(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->managesGallery($user);
    }

    public function view(User $user, GalleryImage $galleryImage): bool
    {
        return $this->managesGallery($user);
    }

    public function create(User $user): bool
    {
        return $this->managesGallery($user);
    }

    public function update(User $user, GalleryImage $galleryImage): bool
    {
        return $this->managesGallery($user);
    }

    public function delete(User $user, GalleryImage $galleryImage): bool
    {
        return $this->managesGallery($user);
    }
}
