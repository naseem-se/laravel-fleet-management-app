<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Journey;
use App\Models\JourneyLocation;
use App\Models\Vehicle;
use App\Services\Concerns\StoresPhotos;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JourneyService
{
    use StoresPhotos;

    public function start(Driver $driver, array $data): Journey
    {
        return DB::transaction(function () use ($driver, $data) {
            // Lock the vehicle row for the duration of this check+create so two
            // simultaneous start requests for the same vehicle can't both succeed.
            $vehicle = Vehicle::where('id', $data['vehicle_id'])->lockForUpdate()->firstOrFail();

            if ($vehicle->activeJourney()->exists()) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['This vehicle already has an active journey.'],
                ]);
            }

            if ($driver->journeys()->where('status', 'active')->exists()) {
                throw ValidationException::withMessages([
                    'driver' => ['You already have an active journey. End it before starting a new one.'],
                ]);
            }

            $photoPath = $this->storePhoto($data['photo'], $driver->company_id, 'journeys/start');

            return Journey::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'status' => 'active',
                'start_km' => $data['start_km'],
                'start_photo_path' => $photoPath,
                'start_lat' => $data['lat'],
                'start_lng' => $data['lng'],
                'start_time' => now(),
            ]);
        });
    }

    public function ping(Journey $journey, array $data): JourneyLocation
    {
        if (! $journey->isActive()) {
            throw ValidationException::withMessages([
                'journey' => ['This journey is no longer active.'],
            ]);
        }

        $location = $journey->locations()->create([
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'speed_kmh' => $data['speed_kmh'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        // Denormalized cache on the vehicle so the admin dashboard's "current
        // location" read never has to scan journey_locations.
        $journey->vehicle()->update([
            'last_lat' => $data['lat'],
            'last_lng' => $data['lng'],
            'last_location_at' => $location->recorded_at,
        ]);

        return $location;
    }

    public function end(Journey $journey, array $data): Journey
    {
        return DB::transaction(function () use ($journey, $data) {
            if (! $journey->isActive()) {
                throw ValidationException::withMessages([
                    'journey' => ['This journey is no longer active.'],
                ]);
            }

            if ($data['end_km'] < $journey->start_km) {
                throw ValidationException::withMessages([
                    'end_km' => ['End odometer reading cannot be less than the start reading.'],
                ]);
            }

            $endPhotoPath = $this->storePhoto($data['photo'], $journey->company_id, 'journeys/end');
            $endTime = now();

            $journey->update([
                'status' => 'completed',
                'end_km' => $data['end_km'],
                'end_photo_path' => $endPhotoPath,
                'end_lat' => $data['lat'],
                'end_lng' => $data['lng'],
                'end_time' => $endTime,
                'total_distance' => $data['end_km'] - $journey->start_km,
                'duration_minutes' => $journey->start_time->diffInMinutes($endTime),
            ]);

            // The odometer's final authoritative value comes from the journey end,
            // not incremented — always the actual reading the driver photographed.
            $journey->vehicle()->update(['current_odometer' => $data['end_km']]);

            return $journey->fresh();
        });
    }
}