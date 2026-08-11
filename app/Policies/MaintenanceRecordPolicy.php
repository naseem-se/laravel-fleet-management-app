<?php

namespace App\Policies;

use App\Models\MaintenanceRecord;
use App\Models\User;

class MaintenanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function view(User $user, MaintenanceRecord $record): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function update(User $user, MaintenanceRecord $record): bool
    {
        return $user->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function delete(User $user, MaintenanceRecord $record): bool
    {
        return $user->hasRole('company_admin');
    }
}