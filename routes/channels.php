<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('company.{companyId}.journeys', function ($user, $companyId) {

    if ((int) $user->company_id !== (int) $companyId) {
        return false;
    }

    if (! $user->hasAnyRole(['company_admin', 'dispatcher'])) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});