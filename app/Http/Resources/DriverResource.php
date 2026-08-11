<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'cnic_number' => $this->cnic_number,
            'license_number' => $this->license_number,
            'license_expiry_date' => $this->license_expiry_date,
            'license_expiring_soon' => $this->isLicenseExpiringSoon(),
            'status' => $this->status,
            'has_login' => ! is_null($this->user_id),
            'assigned_vehicle' => $this->whenLoaded('assignedVehicle', fn () =>
                $this->assignedVehicle->isNotEmpty()
                    ? [
                        'id' => $this->assignedVehicle->first()->id,
                        'registration_number' => $this->assignedVehicle->first()->registration_number,
                    ]
                    : null
            ),
            'created_at' => $this->created_at,
        ];
    }
}