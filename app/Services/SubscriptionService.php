<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{

    public function assign(Company $company, array $data): Subscription
    {
        return DB::transaction(function () use ($company, $data) {
            Subscription::where('company_id', $company->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'subscription_plan_id' => $data['subscription_plan_id'],
                'status' => 'active',
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['ends_at'],
            ]);

            if ($company->status === 'suspended') {
                $company->update(['status' => 'active']);
            }

            return $subscription->fresh('plan');
        });
    }
}