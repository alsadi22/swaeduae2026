<?php

namespace App\Policies;

use App\Models\OrganizationDocument;
use App\Models\User;

class OrganizationDocumentPolicy
{
    public function delete(User $user, OrganizationDocument $document): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        if ($document->organization_id !== $user->organization_id) {
            return false;
        }

        return $user->hasAnyRole(['org-owner', 'org-manager']);
    }
}
