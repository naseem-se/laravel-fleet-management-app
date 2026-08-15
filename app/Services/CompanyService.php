<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyService
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Company::query()
            ->withCount(['vehicles', 'users'])
            ->with('activeSubscription.plan');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function create(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data['name'],
                'legal_name' => $data['legal_name'] ?? null,
                'slug' => $data['slug'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'status' => ! empty($data['trial_days']) ? 'trial' : 'active',
            ]);

            $admin = User::create([
                'company_id' => $company->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'status' => 'active',
            ]);
            
            $admin->assignRole('company_admin');
            $admin->sendEmailVerificationNotification();

            if (! empty($data['subscription_plan_id'])) {
                $trialDays = $data['trial_days'] ?? 0;

                Subscription::create([
                    'company_id' => $company->id,
                    'subscription_plan_id' => $data['subscription_plan_id'],
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(), // billing cycle renewal handled in Step 8's ExpireSubscriptions job (already scheduled) or a future billing integration
                    'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
                ]);
            }

            return $company->fresh(['activeSubscription.plan']);
        });
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function suspend(Company $company): Company
    {
        $company->update(['status' => 'suspended']);

        return $company;
    }

    public function activate(Company $company): Company
    {
        $company->update(['status' => 'active']);

        return $company;
    }

    public function platformStats(): array
    {
        return [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', 'active')->count(),
            'trial_companies' => Company::where('status', 'trial')->count(),
            'suspended_companies' => Company::where('status', 'suspended')->count(),
            'total_vehicles' => \App\Models\Vehicle::withoutGlobalScopes()->count(),
            'total_drivers' => \App\Models\Driver::withoutGlobalScopes()->count(),
            'active_journeys_now' => \App\Models\Journey::withoutGlobalScopes()->where('status', 'active')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->where('ends_at', '>', now())->count(),
        ];
    }
}