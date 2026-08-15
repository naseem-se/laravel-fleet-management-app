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

            return FuelEntry::create([
                'vehicle_id' => $data['vehicle_id'],
                'journey_id' => $data['journey_id'] ?? null,
                'driver_id' => $driver->id,
                'quantity_litres' => $data['quantity_litres'],
                'rate_per_litre' => $data['rate_per_litre'],
                'total_cost' => round($data['quantity_litres'] * $data['rate_per_litre'], 2),
                'odometer_reading' => $data['odometer_reading'],
                'receipt_photo_path' => $receiptPath,
                'entry_time' => now(),
            ]);
        });
    }

    public function createManual(int $companyId, array $data): FuelEntry
    {
        return DB::transaction(function () use ($companyId, $data) {
            return FuelEntry::create([
                'vehicle_id' => $data['vehicle_id'],
                'journey_id' => $data['journey_id'] ?? null,
                'driver_id' => $data['driver_id'],
                'quantity_litres' => $data['quantity_litres'],
                'rate_per_litre' => $data['rate_per_litre'],
                'total_cost' => round($data['quantity_litres'] * $data['rate_per_litre'], 2),
                'odometer_reading' => $data['odometer_reading'],
                'entry_time' => $data['entry_time'] ?? now(),
            ]);
        });
    }

    public function update(FuelEntry $entry, array $data): FuelEntry
    {
        return DB::transaction(function () use ($entry, $data) {
            if (isset($data['quantity_litres']) || isset($data['rate_per_litre'])) {
                $litres = $data['quantity_litres'] ?? $entry->quantity_litres;
                $rate = $data['rate_per_litre'] ?? $entry->rate_per_litre;
                $data['total_cost'] = round($litres * $rate, 2);
            }

            if (isset($data['receipt_photo'])) {
                $data['receipt_photo_path'] = $this->replacePhoto(
                    $data['receipt_photo'],
                    $entry->receipt_photo_path,
                    $entry->company_id,
                    'fuel-receipts'
                );
            }
            unset($data['receipt_photo']);

            $entry->update($data);

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
}