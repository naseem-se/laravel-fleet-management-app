<?php

namespace App\Policies;

use App\Models\Journey;
use App\Models\User;

class JourneyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher', 'driver']);
    }

    public function view(User $user, Journey $journey): bool
    {
        if ($user->hasAnyRole(['company_admin', 'dispatcher'])) {
            return true;
        }

        return $user->driver && $user->driver->id === $journey->driver_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('driver') && $user->driver !== null;
    }

    /**
     * Custom abilities (not CRUD) — only the owning driver may ping/end
     * their own ACTIVE journey. Admins never do this; it's a physical
     * action tied to being in the vehicle.
     */
    public function update(User $user, Journey $journey): bool
    {
        return $user->hasRole('driver')
            && $user->driver
            && $user->driver->id === $journey->driver_id
            && $journey->isActive();
    }
}