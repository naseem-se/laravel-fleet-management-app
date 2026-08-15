<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'id' => $this->vehicle->id,
                'registration_number' => $this->vehicle->registration_number,
            ]),
            'driver' => $this->whenLoaded('driver', fn () => [
                'id' => $this->driver->id,
                'name' => $this->driver->name,
            ]),
            'start' => [
                'km' => $this->start_km,
                'photo_url' => FileUrl::for($this->start_photo_path),
                'lat' => $this->start_lat,
                'lng' => $this->start_lng,
                'time' => $this->start_time,
            ],
            'end' => $this->when($this->status === 'completed', [
                'km' => $this->end_km,
                'photo_url' => FileUrl::for($this->end_photo_path),
                'lat' => $this->end_lat,
                'lng' => $this->end_lng,
                'time' => $this->end_time,
            ]),
            'total_distance' => $this->total_distance,
            'duration_minutes' => $this->duration_minutes,
            'last_location' => $this->whenLoaded('vehicle', fn () => $this->vehicle->last_location_at ? [
                'lat' => $this->vehicle->last_lat,
                'lng' => $this->vehicle->last_lng,
                'recorded_at' => $this->vehicle->last_location_at,
            ] : null),
            'location_summary' => $this->whenLoaded('locationSummary', fn () => $this->locationSummary ? [
                'point_count' => $this->locationSummary->point_count,
                'bounds' => [
                    'min_lat' => $this->locationSummary->min_lat,
                    'max_lat' => $this->locationSummary->max_lat,
                    'min_lng' => $this->locationSummary->min_lng,
                    'max_lng' => $this->locationSummary->max_lng,
                ],
                'max_speed_kmh' => $this->locationSummary->max_speed_kmh,
                'avg_speed_kmh' => $this->locationSummary->avg_speed_kmh,
                'archived_at' => $this->locationSummary->archived_at,
            ] : null),
        ];
    }
}