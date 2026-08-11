<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'slug' => $this->slug,
            'status' => $this->status,
            'timezone' => $this->timezone,
            'active_subscription' => $this->whenLoaded('activeSubscription', fn () =>
                $this->activeSubscription ? [
                    'plan' => $this->activeSubscription->plan->name ?? null,
                    'status' => $this->activeSubscription->status,
                    'ends_at' => $this->activeSubscription->ends_at,
                ] : null
            ),
            'vehicle_count' => $this->when(isset($this->vehicles_count), $this->vehicles_count),
            'user_count' => $this->when(isset($this->users_count), $this->users_count),
            'created_at' => $this->created_at,
        ];
    }
}