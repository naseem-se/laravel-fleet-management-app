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
            'current_fuel_litres' => $this->current_fuel_litres,
            'mileage_rated' => $this->mileage_rated,
            'avg_kmpl_cached' => $this->avg_kmpl_cached,
            'status' => $this->status,
            'last_location' => $this->when($this->last_location_at, [
                'lat' => $this->last_lat !== null ? (float) $this->last_lat : null,
                'lng' => $this->last_lng !== null ? (float) $this->last_lng : null,
                'recorded_at' => $this->last_location_at,
                'accuracy_meters' => $this->last_accuracy_meters !== null ? (float) $this->last_accuracy_meters : null,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}