<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\FuelEntry;
use App\Services\Concerns\StoresPhotos;
use Illuminate\Support\Facades\DB;

class FuelService
{
    use StoresPhotos;

    public function create(Driver $driver, array $data): FuelEntry
    {
        return DB::transaction(function () use ($driver, $data) {
            $receiptPath = $this->storePhoto($data['receipt_photo'], $driver->company_id, 'fuel-receipts');

            $litres = round($data['total_price'] / $data['rate_per_litre'], 2);

            $entry = FuelEntry::create([
                'vehicle_id' => $data['vehicle_id'],
                'journey_id' => $data['journey_id'] ?? null,
                'driver_id' => $driver->id,
                'quantity_litres' => $litres,
                'rate_per_litre' => $data['rate_per_litre'],
                'total_cost' => $data['total_price'],
                'odometer_reading' => $data['odometer_reading'],
                'receipt_photo_path' => $receiptPath,
                'entry_time' => now(),
            ]);

            $this->bumpVehicleFuelLevel($entry);

            return $entry;
        });
    }

    public function createManual(int $companyId, array $data): FuelEntry
    {
        return DB::transaction(function () use ($companyId, $data) {
            $receiptPath = isset($data['receipt_photo'])
                ? $this->storePhoto($data['receipt_photo'], $companyId, 'fuel-receipts')
                : null;

            $litres = round($data['total_price'] / $data['rate_per_litre'], 2);

            $entry = FuelEntry::create([
                'vehicle_id' => $data['vehicle_id'],
                'journey_id' => $data['journey_id'] ?? null,
                'driver_id' => $data['driver_id'],
                'quantity_litres' => $litres,
                'rate_per_litre' => $data['rate_per_litre'],
                'total_cost' => $data['total_price'],
                'odometer_reading' => $data['odometer_reading'],
                'receipt_photo_path' => $receiptPath,
                'entry_time' => $data['entry_time'] ?? now(),
            ]);

            $this->bumpVehicleFuelLevel($entry);

            return $entry;
        });
    }

    public function update(FuelEntry $entry, array $data): FuelEntry
    {
        return DB::transaction(function () use ($entry, $data) {
            $update = [];

            if (isset($data['total_price']) || isset($data['rate_per_litre'])) {
                $totalPrice = $data['total_price'] ?? $entry->total_cost;
                $rate = $data['rate_per_litre'] ?? $entry->rate_per_litre;

                $update['total_cost'] = $totalPrice;
                $update['rate_per_litre'] = $rate;
                $update['quantity_litres'] = round($totalPrice / $rate, 2);
            }

            if (isset($data['odometer_reading'])) {
                $update['odometer_reading'] = $data['odometer_reading'];
            }

            if (isset($data['entry_time'])) {
                $update['entry_time'] = $data['entry_time'];
            }

            if (isset($data['receipt_photo'])) {
                $update['receipt_photo_path'] = $this->replacePhoto(
                    $data['receipt_photo'],
                    $entry->receipt_photo_path,
                    $entry->company_id,
                    'fuel-receipts'
                );
            }

            $entry->update($update);

            return $entry->fresh();
        });
    }

    public function delete(FuelEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $this->deletePhoto($entry->receipt_photo_path);
            $entry->delete();
        });
    }

    /**
     * A rough running estimate, not a precise fuel-gauge simulation — we
     * have no way to measure actual consumption between fills, so this
     * only ever goes UP (fuel purchased is added to the tank). It's
     * informational, shown next to the vehicle's tank capacity so an
     * admin has a sense of "roughly how full is this vehicle," not a
     * claim of exact accuracy.
     */
    protected function bumpVehicleFuelLevel(FuelEntry $entry): void
    {
        $vehicle = $entry->vehicle;
        if (! $vehicle) {
            return;
        }

        $newLevel = (float) $vehicle->current_fuel_litres + (float) $entry->quantity_litres;

        if ($vehicle->tank_capacity_litres) {
            $newLevel = min($newLevel, (float) $vehicle->tank_capacity_litres);
        }

        $vehicle->update(['current_fuel_litres' => $newLevel]);
    }
}