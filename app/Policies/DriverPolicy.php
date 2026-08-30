<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function view(User $user, Driver $driver): bool
    {
        if ($user->hasAnyRole(['company_admin', 'dispatcher'])) {
            return true;
        }
        
        return $user->driver && $user->driver->id === $driver->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('company_admin');
    }

    public function update(User $user, Driver $driver): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $user->hasRole('company_admin');
    }

    public function updatePhoto(User $user, Driver $driver): bool
    {
        if ($user->hasAnyRole(['company_admin', 'dispatcher'])) {
            return true;
        }

        return $user->driver && $user->driver->id === $driver->id;
    }
}