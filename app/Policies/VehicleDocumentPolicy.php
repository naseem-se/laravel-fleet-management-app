<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleDocument;

class VehicleDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function update(User $user, VehicleDocument $document): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function delete(User $user, VehicleDocument $document): bool
    {
        return $user->hasRole('company_admin');
    }
}