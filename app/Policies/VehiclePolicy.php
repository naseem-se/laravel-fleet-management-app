<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher', 'driver']);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher', 'driver']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasRole('company_admin');
    }
}