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

        // A driver can view their own record (e.g. "my profile" / performance screen)
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
}