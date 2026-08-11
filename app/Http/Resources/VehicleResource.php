<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'qr_code_value' => $this->qr_code_value,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'vehicle_type' => $this->vehicle_type,
            'engine_number' => $this->engine_number,
            'chassis_number' => $this->chassis_number,
            'fuel_type' => $this->fuel_type,
            'tank_capacity_litres' => $this->tank_capacity_litres,
            'current_odometer' => $this->current_odometer,
            'avg_kmpl_cached' => $this->avg_kmpl_cached,
            'status' => $this->status,
            'last_location' => $this->when($this->last_location_at, [
                'lat' => $this->last_lat,
                'lng' => $this->last_lng,
                'recorded_at' => $this->last_location_at,
            ]),
            'assigned_driver' => $this->whenLoaded('assignedDriver', fn () => [
                'id' => $this->assignedDriver->id,
                'name' => $this->assignedDriver->name,
                'phone' => $this->assignedDriver->phone,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}