<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\FuelEntry;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Carbon\Carbon;

class ReportService
{
    public function vehicleReport(Vehicle $vehicle, string $from, string $to): array
    {
        [$fromDt, $toDt] = $this->dayBounds($from, $to);

        $journeysQuery = $vehicle->journeys()
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromDt, $toDt]);

        $fuelQuery = $vehicle->fuelEntries()->whereBetween('entry_time', [$fromDt, $toDt]);
        $maintenanceQuery = $vehicle->maintenanceRecords()->whereBetween('service_date', [$from, $to]);

        $totalDistance = (clone $journeysQuery)->sum('total_distance');
        $totalFuelLitres = (clone $fuelQuery)->sum('quantity_litres');
        $totalFuelCost = (clone $fuelQuery)->sum('total_cost');
        $totalMaintenanceCost = (clone $maintenanceQuery)->sum('cost');

        $actualKmpl = $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null;

        $mileageVariancePercent = null;
        if ($actualKmpl !== null && $vehicle->mileage_rated > 0) {
            $mileageVariancePercent = round((($actualKmpl - $vehicle->mileage_rated) / $vehicle->mileage_rated) * 100, 1);
        }

        $journeys = $journeysQuery->with('driver:id,name')
            ->orderBy('start_time')
            ->get([
                'id', 'driver_id', 'purpose', 'detail_of_journey', 'officer_name', 'signature',
                'start_time', 'end_time', 'start_km', 'end_km', 'total_distance',
                'start_photo_path', 'end_photo_path',
            ]);

        $journeys->each(function ($j) {
            $clean = fn ($v) => (is_string($v) && in_array(strtolower(trim($v)), ['undefined', 'null'], true)) ? null : $v;

            $j->driver_name = $j->driver?->name ?? '-';
            $j->purpose_display = $clean($j->purpose) ?: '-';
            $j->detail_display = $clean($j->detail_of_journey) ?: '-';
            $j->officer_display = $clean($j->officer_name) ?: '-';
            $j->signature_display = $clean($j->signature) ?: '-';
            $j->start_km_display = $j->start_km ?? '-';
            $j->end_km_display = $j->end_km ?? '-';
            $j->distance_display = $j->total_distance ?? '-';
            $j->start_photo_url = \App\Support\FileUrl::for($j->start_photo_path);
            $j->end_photo_url = \App\Support\FileUrl::for($j->end_photo_path);
        });

        $fuelEntries = (clone $fuelQuery)->with(['journey:id,start_time', 'driver:id,name'])->orderBy('entry_time')->get();

        $fuelEntries->each(function ($f) {
            $f->receipt_url = \App\Support\FileUrl::for($f->receipt_photo_path);
            $f->linked_journey_id = $f->journey_id;
            $f->linked_journey_date = $f->journey?->start_time?->format('Y-m-d');
        });

        return [
            'vehicle' => [
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'current_odometer' => $vehicle->current_odometer,
                'current_fuel_litres' => $vehicle->current_fuel_litres,
                'mileage_rated' => $vehicle->mileage_rated,
                'tank_capacity_litres' => $vehicle->tank_capacity_litres,
            ],
            'period' => ['from' => $from, 'to' => $to],
            'total_journeys' => $journeys->count(),
            'total_distance' => (float) $totalDistance,
            'total_fuel_litres' => (float) $totalFuelLitres,
            'total_fuel_cost' => (float) $totalFuelCost,
            'kmpl' => $actualKmpl,
            'mileage_rated' => $vehicle->mileage_rated,
            'mileage_variance_percent' => $mileageVariancePercent,
            'total_maintenance_cost' => (float) $totalMaintenanceCost,
            'journeys' => $journeys,
            'fuel_entries' => $fuelEntries,
        ];
    }

    public function driverReport(Driver $driver, string $from, string $to): array
    {
        [$fromDt, $toDt] = $this->dayBounds($from, $to);

        $journeys = $driver->journeys()
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromDt, $toDt]);

        $fuel = $driver->fuelEntries()->whereBetween('entry_time', [$fromDt, $toDt]);

        return [
            'driver' => ['id' => $driver->id, 'name' => $driver->name, 'phone' => $driver->phone],
            'period' => ['from' => $from, 'to' => $to],
            'total_journeys' => (clone $journeys)->count(),
            'total_distance' => (float) (clone $journeys)->sum('total_distance'),
            'total_fuel_litres' => (float) (clone $fuel)->sum('quantity_litres'),
            'total_fuel_cost' => (float) (clone $fuel)->sum('total_cost'),
            'journeys' => $journeys->with('vehicle:id,registration_number')->get(),
        ];
    }

    public function fuelReport(string $from, string $to, ?int $vehicleId = null): array
    {
        [$fromDt, $toDt] = $this->dayBounds($from, $to);

        $query = FuelEntry::query()
            ->whereBetween('entry_time', [$fromDt, $toDt])
            ->with(['vehicle:id,registration_number', 'driver:id,name', 'journey:id,start_time']);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $entries = $query->orderBy('entry_time')->get();

        $entries->each(function ($e) {
            $e->receipt_url = \App\Support\FileUrl::for($e->receipt_photo_path);
            $e->linked_journey_date = $e->journey?->start_time?->format('Y-m-d');
        });

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total_litres' => (float) $entries->sum('quantity_litres'),
            'total_cost' => (float) $entries->sum('total_cost'),
            'entries' => $entries,
        ];
    }

    public function maintenanceReport(string $from, string $to, ?int $vehicleId = null): array
    {
        $query = MaintenanceRecord::query()
            ->whereBetween('service_date', [$from, $to])
            ->with('vehicle:id,registration_number');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $records = $query->orderBy('service_date')->get();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total_cost' => (float) $records->sum('cost'),
            'records' => $records,
        ];
    }

    public function fleetSummary(string $month): array
    {
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $journeys = \App\Models\Journey::where('status', 'completed')->whereBetween('start_time', [$start, $end]);
        $fuel = FuelEntry::whereBetween('entry_time', [$start, $end]);
        $maintenance = MaintenanceRecord::whereBetween('service_date', [$start->toDateString(), $end->toDateString()]);

        $totalDistance = (clone $journeys)->sum('total_distance');
        $totalFuelLitres = (clone $fuel)->sum('quantity_litres');

        return [
            'month' => $start->format('Y-m'),
            'vehicles' => [
                'total' => Vehicle::count(),
                'active' => Vehicle::where('status', 'active')->count(),
                'inactive' => Vehicle::where('status', 'inactive')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ],
            'total_journeys' => (clone $journeys)->count(),
            'total_distance' => (float) $totalDistance,
            'total_fuel_litres' => (float) $totalFuelLitres,
            'total_fuel_cost' => (float) (clone $fuel)->sum('total_cost'),
            'fleet_avg_kmpl' => $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null,
            'total_maintenance_cost' => (float) (clone $maintenance)->sum('cost'),
            'per_vehicle' => Vehicle::withSum(['journeys as month_distance' => fn ($q) =>
                    $q->where('status', 'completed')->whereBetween('start_time', [$start, $end]),
                ], 'total_distance')
                ->withSum(['fuelEntries as month_fuel_litres' => fn ($q) =>
                    $q->whereBetween('entry_time', [$start, $end]),
                ], 'quantity_litres')
                ->get(['id', 'registration_number'])
                ->map(fn ($v) => [
                    'vehicle' => $v->registration_number,
                    'distance' => (float) ($v->month_distance ?? 0),
                    'fuel_litres' => (float) ($v->month_fuel_litres ?? 0),
                    'kmpl' => $v->month_fuel_litres > 0 ? round($v->month_distance / $v->month_fuel_litres, 2) : null,
                ]),
        ];
    }

    public function dashboardOverview(): array
    {
        $totalDistance = \App\Models\Journey::where('status', 'completed')->sum('total_distance');
        $totalFuelLitres = FuelEntry::sum('quantity_litres');
        $totalFuelCost = FuelEntry::sum('total_cost');
        $totalMaintenanceCost = MaintenanceRecord::sum('cost');

        return [
            'vehicles' => [
                'total' => Vehicle::count(),
                'active' => Vehicle::where('status', 'active')->count(),
                'inactive' => Vehicle::where('status', 'inactive')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ],
            'drivers' => [
                'total' => Driver::count(),
                'active' => Driver::where('status', 'active')->count(),
            ],
            'total_journeys' => \App\Models\Journey::where('status', 'completed')->count(),
            'active_journeys' => \App\Models\Journey::where('status', 'active')->count(),
            'total_distance' => (float) $totalDistance,
            'total_fuel_litres' => (float) $totalFuelLitres,
            'total_fuel_cost' => (float) $totalFuelCost,
            'fleet_avg_kmpl' => $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null,
            'total_maintenance_cost' => (float) $totalMaintenanceCost,
        ];
    }

    protected function dayBounds(string $from, string $to): array
    {
        return ["{$from} 00:00:00", "{$to} 23:59:59"];
    }
}