<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Automatically constrain every query on a tenant model to the
     * authenticated user's company. Super admins (company_id === null)
     * are left unrestricted — they only reach admin endpoints that are
     * explicitly guarded by the super_admin role/policy anyway.
     *
     * For console commands / queued jobs that run without an authenticated
     * user, this scope does nothing — those call sites must filter by
     * company_id explicitly (e.g. Vehicle::withoutGlobalScope(CompanyScope::class)
     * ->where('company_id', $companyId)).
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->company_id !== null) {
            $builder->where($model->getTable().'.company_id', Auth::user()->company_id);
        }
    }
}