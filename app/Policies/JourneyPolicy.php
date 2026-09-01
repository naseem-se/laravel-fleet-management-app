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

    /** Only the owning driver may ping/end their own currently-active journey. */
    public function update(User $user, Journey $journey): bool
    {
        return $user->hasRole('driver')
            && $user->driver
            && $user->driver->id === $journey->driver_id
            && $journey->isActive();
    }

    /** Admin correcting a logged journey's details after the fact — a separate ability from the driver's live update above. */
    public function updateDetails(User $user, Journey $journey): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function delete(User $user, Journey $journey): bool
    {
        return $user->hasRole('company_admin');
    }
}