<?php

namespace App\Services;

use App\Models\MaintenanceRecord;
use App\Models\Reminder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = MaintenanceRecord::query()->with('vehicle');

        if (! empty($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderByDesc('service_date')->paginate($perPage);
    }

    public function create(array $data): MaintenanceRecord
    {
        return DB::transaction(function () use ($data) {
            $record = MaintenanceRecord::create($data);

            $this->syncReminder($record);

            return $record;
        });
    }

    public function update(MaintenanceRecord $record, array $data): MaintenanceRecord
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update($data);

            $this->syncReminder($record);

            return $record->fresh();
        });
    }

    public function delete(MaintenanceRecord $record): void
    {
        DB::transaction(function () use ($record) {
            Reminder::where('reminder_type', 'maintenance')
                ->where('reference_type', MaintenanceRecord::class)
                ->where('reference_id', $record->id)
                ->delete();

            $record->delete();
        });
    }

    /**
     * Keeps a single pending reminder row in sync with this record's next
     * due date/km, rather than letting them drift or duplicate on edit.
     */
    protected function syncReminder(MaintenanceRecord $record): void
    {
        Reminder::where('reminder_type', 'maintenance')
            ->where('reference_type', MaintenanceRecord::class)
            ->where('reference_id', $record->id)
            ->where('status', 'pending')
            ->delete();

        if ($record->next_service_date || $record->next_service_km) {
            Reminder::create([
                'reminder_type' => 'maintenance',
                'reference_type' => MaintenanceRecord::class,
                'reference_id' => $record->id,
                'due_date' => $record->next_service_date,
                'due_km' => $record->next_service_km,
                'status' => 'pending',
            ]);
        }
    }
}