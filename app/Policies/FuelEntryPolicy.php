<?php

namespace App\Policies;

use App\Models\User;

class FuelEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }
}