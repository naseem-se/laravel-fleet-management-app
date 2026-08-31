<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyResource extends JsonResource
{
    protected function clean($value)
    {
        if (is_string($value) && in_array(strtolower(trim($value)), ['undefined', 'null'], true)) {
            return null;
        }
        return $value;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'purpose' => $this->clean($this->purpose),
            'detail_of_journey' => $this->clean($this->detail_of_journey),
            'officer_name' => $this->clean($this->officer_name),
            'signature' => $this->clean($this->signature),
            'pol_drawn' => $this->pol_drawn,
            'pol_invoice_photo_url' => FileUrl::for($this->pol_invoice_photo_path),
            'remarks' => $this->clean($this->remarks),
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'id' => $this->vehicle->id,
                'registration_number' => $this->vehicle->registration_number,
            ]),
            'driver' => $this->whenLoaded('driver', fn () => [
                'id' => $this->driver->id,
                'name' => $this->driver->name,
                'profile_photo_url' => FileUrl::for($this->driver->profile_photo_path),
            ]),
            'start' => [
                'km' => $this->start_km,
                'photo_url' => FileUrl::for($this->start_photo_path),
                'lat' => $this->start_lat !== null ? (float) $this->start_lat : null,
                'lng' => $this->start_lng !== null ? (float) $this->start_lng : null,
                'time' => $this->start_time,
            ],
            'end' => $this->when($this->status === 'completed', [
                'km' => $this->end_km,
                'photo_url' => FileUrl::for($this->end_photo_path),
                'lat' => $this->end_lat !== null ? (float) $this->end_lat : null,
                'lng' => $this->end_lng !== null ? (float) $this->end_lng : null,
                'time' => $this->end_time,
            ]),
            'total_distance' => $this->total_distance,
            'duration_minutes' => $this->duration_minutes,
            'last_location' => $this->whenLoaded('vehicle', fn () => $this->vehicle->last_location_at ? [
                'lat' => (float) $this->vehicle->last_lat,
                'lng' => (float) $this->vehicle->last_lng,
                'recorded_at' => $this->vehicle->last_location_at,
                'accuracy_meters' => $this->vehicle->last_accuracy_meters !== null ? (float) $this->vehicle->last_accuracy_meters : null,
            ] : null),
        ];
    }
}