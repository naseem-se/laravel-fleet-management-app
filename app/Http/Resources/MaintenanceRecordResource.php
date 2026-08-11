<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'type' => $this->type,
            'description' => $this->description,
            'cost' => $this->cost,
            'odometer_at_service' => $this->odometer_at_service,
            'service_date' => $this->service_date,
            'next_service_date' => $this->next_service_date,
            'next_service_km' => $this->next_service_km,
            'performed_by' => $this->performed_by,
            'created_at' => $this->created_at,
        ];
    }
}