<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * CompanyScope already restricts *which* vehicles a query returns, so
     * these checks are about *role*, not tenancy — a company_admin/dispatcher
     * can manage vehicles, a driver can only view the one assigned to them.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher', 'driver']);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole(['company_admin', 'dispatcher'])) {
            return true;
        }

        return $user->driver && $vehicle->assigned_driver_id === $user->driver->id;
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