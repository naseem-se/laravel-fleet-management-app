<?php

namespace App\Services;

use App\Events\JourneyLocationUpdated;
use App\Events\JourneyStatusChanged;
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

            if ((float) $data['start_km'] < (float) $vehicle->current_odometer) {
                throw ValidationException::withMessages([
                    'start_km' => ["Starting odometer cannot be less than the vehicle's last recorded reading of {$vehicle->current_odometer} km."],
                ]);
            }

            $photoPath = $this->storePhoto($data['photo'], $driver->company_id, 'journeys/start');

            $journey = Journey::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'purpose' => $data['purpose'] ?? null,
                'detail_of_journey' => $data['detail_of_journey'] ?? null,
                'officer_name' => $data['officer_name'] ?? null,
                'status' => 'active',
                'start_km' => $data['start_km'],
                'start_photo_path' => $photoPath,
                'start_lat' => $data['lat'],
                'start_lng' => $data['lng'],
                'start_time' => now(),
            ]);

            broadcast(new JourneyStatusChanged($journey->id, $driver->company_id, 'started'))->toOthers();

            return $journey;
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
            'accuracy_meters' => $data['accuracy'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        $journey->vehicle()->update([
            'last_lat' => $data['lat'],
            'last_lng' => $data['lng'],
            'last_location_at' => $location->recorded_at,
            'last_accuracy_meters' => $data['accuracy'] ?? null,
        ]);

        broadcast(new JourneyLocationUpdated(
            $journey->id,
            $journey->company_id,
            (float) $data['lat'],
            (float) $data['lng'],
            isset($data['speed_kmh']) ? (float) $data['speed_kmh'] : null,
            $location->recorded_at->toIso8601String(),
            isset($data['accuracy']) ? (float) $data['accuracy'] : null,
        ))->toOthers();

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
                'signature' => $data['signature'] ?? null,
                'pol_drawn' => $data['pol_drawn'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $journey->vehicle()->update(['current_odometer' => $data['end_km']]);

            broadcast(new JourneyStatusChanged($journey->id, $journey->company_id, 'ended'))->toOthers();

            return $journey->fresh();
        });
    }

    public function delete(Journey $journey): void
    {
        DB::transaction(function () use ($journey) {
            $this->deletePhoto($journey->start_photo_path);
            $this->deletePhoto($journey->end_photo_path);

            foreach ($journey->fuelEntries as $fuelEntry) {
                $this->deletePhoto($fuelEntry->receipt_photo_path);
            }
            $journey->fuelEntries()->delete();
            $journey->locations()->delete();

            $journey->delete();
        });
    }
}