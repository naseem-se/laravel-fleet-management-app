<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DriverService
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Driver::query()->with('assignedVehicle');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function create(Company $company, array $data): Driver
    {
        $this->assertWithinPlanLimit($company);

        return DB::transaction(function () use ($company, $data) {
            $userId = null;

            if (! empty($data['create_login'])) {
                $user = User::create([
                    'company_id' => $company->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                ]);
                $user->assignRole('driver');
                $userId = $user->id;
            }

            $driver = Driver::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'cnic_number' => $data['cnic_number'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'license_expiry_date' => $data['license_expiry_date'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            return $driver->fresh('assignedVehicle');
        });
    }

    public function update(Driver $driver, array $data): Driver
    {
        $driver->update($data);

        return $driver->fresh('assignedVehicle');
    }

    public function delete(Driver $driver): void
    {
        if ($driver->assignedVehicle()->exists()) {
            throw ValidationException::withMessages([
                'driver' => ['Unassign this driver from their vehicle before deleting.'],
            ]);
        }

        $driver->delete(); // soft delete — journey/fuel history stays intact
    }

    protected function assertWithinPlanLimit(Company $company): void
    {
        $subscription = $company->activeSubscription;

        if (! $subscription) {
            throw ValidationException::withMessages([
                'company' => ['No active subscription found for this company.'],
            ]);
        }

        // Counts users, not drivers — a driver with a login IS a user; a
        // driver without one shouldn't count against the seat limit at all.
        $currentUserCount = User::query()->count(); // already company-scoped

        if ($currentUserCount >= $subscription->plan->max_users) {
            throw ValidationException::withMessages([
                'plan_limit' => ["Your plan allows a maximum of {$subscription->plan->max_users} users. Upgrade to add more."],
            ]);
        }
    }
}