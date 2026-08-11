<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'max_vehicles' => $this->max_vehicles,
            'max_users' => $this->max_users,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'features' => $this->features,
            'is_active' => $this->is_active,
        ];
    }
}