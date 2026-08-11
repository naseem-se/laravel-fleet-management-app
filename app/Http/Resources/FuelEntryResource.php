<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'journey_id' => $this->journey_id,
            'driver_id' => $this->driver_id,
            'quantity_litres' => $this->quantity_litres,
            'rate_per_litre' => $this->rate_per_litre,
            'total_cost' => $this->total_cost,
            'odometer_reading' => $this->odometer_reading,
            'receipt_photo_url' => FileUrl::for($this->receipt_photo_path),
            'entry_time' => $this->entry_time,
        ];
    }
}