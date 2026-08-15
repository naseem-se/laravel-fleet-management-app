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
                    'phone' => $data['phone'],
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                ]);
                $user->assignRole('driver');
                $user->sendEmailVerificationNotification();
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
        return DB::transaction(function () use ($driver, $data) {
            $driver->update($data);

            if (array_key_exists('phone', $data) && $driver->user_id) {
                User::where('id', $driver->user_id)->update(['phone' => $data['phone']]);
            }

            return $driver->fresh('assignedVehicle');
        });
    }

    public function delete(Driver $driver): void
    {
        if ($driver->assignedVehicle()->exists()) {
            throw ValidationException::withMessages([
                'driver' => ['Unassign this driver from their vehicle before deleting.'],
            ]);
        }

        $driver->delete();
    }

    public function createLogin(Driver $driver, array $data): Driver
    {
        if ($driver->user_id) {
            throw ValidationException::withMessages([
                'driver' => ['This driver already has a login.'],
            ]);
        }

        return DB::transaction(function () use ($driver, $data) {
            $user = User::create([
                'company_id' => $driver->company_id,
                'name' => $driver->name,
                'email' => $data['email'],
                'phone' => $driver->phone, // was missing — this is why the profile phone field was always blank
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);
            $user->assignRole('driver');
            $user->sendEmailVerificationNotification();

            $driver->update(['user_id' => $user->id]);

            return $driver->fresh('assignedVehicle');
        });
    }

    public function updateLogin(Driver $driver, array $data): Driver
    {
        if (! $driver->user_id) {
            throw ValidationException::withMessages([
                'driver' => ['This driver does not have a login yet.'],
            ]);
        }

        return DB::transaction(function () use ($driver, $data) {
            $user = User::findOrFail($driver->user_id);

            if (! empty($data['email'])) {
                $user->email = $data['email'];
            }
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            return $driver->fresh('assignedVehicle');
        });
    }

    protected function assertWithinPlanLimit(Company $company): void
    {
        $subscription = $company->activeSubscription;

        if (! $subscription) {
            throw ValidationException::withMessages([
                'company' => ['No active subscription found for this company.'],
            ]);
        }

        $currentUserCount = User::query()->count();

        if ($currentUserCount >= $subscription->plan->max_users) {
            throw ValidationException::withMessages([
                'plan_limit' => ["Your plan allows a maximum of {$subscription->plan->max_users} users. Upgrade to add more."],
            ]);
        }
    }
}