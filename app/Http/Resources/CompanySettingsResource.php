<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'slug' => $this->slug,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'gps_ping_interval_seconds' => $this->settings['gps_ping_interval_seconds'] ?? 300,
            'distance_unit' => $this->settings['distance_unit'] ?? 'km',
            'active_subscription' => $this->whenLoaded('activeSubscription', fn () => $this->activeSubscription ? [
                'plan' => $this->activeSubscription->plan->name ?? null,
                'ends_at' => $this->activeSubscription->ends_at,
                'status' => $this->activeSubscription->status,
            ] : null),
        ];
    }
}