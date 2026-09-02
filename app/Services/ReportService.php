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

        $fuelEntries = (clone $fuelQuery)->with(['journey:id,start_time', 'driver:id,name'])->orderBy('entry_time')->get();

        // Build a map of journey_id -> [fuel entry ids] so each journey row
        // can list every fuel purchase linked to it (a trip can have more
        // than one fuel stop), and each fuel row can point back to its one
        // trip. Anchors use the record's real database ID — unique,
        // stable, and needs no separate numbering scheme.
        $fuelByJourney = $fuelEntries->whereNotNull('journey_id')->groupBy('journey_id');

        $journeys->each(function ($j) use ($fuelByJourney) {
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
            $j->linked_fuel_ids = $fuelByJourney->get($j->id, collect())->pluck('id')->values();
        });

        $fuelEntries->each(function ($f) {
            $f->receipt_url = \App\Support\FileUrl::for($f->receipt_photo_path);
            $f->linked_journey_id = $f->journey_id;
            $f->linked_journey_date = $f->journey?->start_time?->format('M j, Y');
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

        $journeysQuery = $driver->journeys()
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromDt, $toDt]);

        $fuelQuery = $driver->fuelEntries()->whereBetween('entry_time', [$fromDt, $toDt]);

        $totalDistance = (clone $journeysQuery)->sum('total_distance');
        $totalFuelLitres = (clone $fuelQuery)->sum('quantity_litres');
        $totalFuelCost = (clone $fuelQuery)->sum('total_cost');
        $avgKmpl = $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null;

        $journeys = $journeysQuery->with('vehicle:id,registration_number')
            ->orderBy('start_time')
            ->get(['id', 'vehicle_id', 'purpose', 'start_time', 'end_time', 'start_km', 'end_km', 'total_distance']);

        $vehicleBreakdown = $journeys->groupBy('vehicle_id')->map(function ($group) {
            $first = $group->first();
            return [
                'vehicle' => $first->vehicle?->registration_number ?? '-',
                'trips' => $group->count(),
                'distance' => (float) $group->sum('total_distance'),
            ];
        })->values();

        $fuelEntries = (clone $fuelQuery)->with(['vehicle:id,registration_number', 'journey:id,start_time'])
            ->orderBy('entry_time')->get();

        $fuelByJourney = $fuelEntries->whereNotNull('journey_id')->groupBy('journey_id');

        $journeys->each(function ($j) use ($fuelByJourney) {
            $j->vehicle_registration = $j->vehicle?->registration_number ?? '-';
            $j->purpose_display = $j->purpose ?: '-';
            $j->distance_display = $j->total_distance ?? '-';
            $j->linked_fuel_ids = $fuelByJourney->get($j->id, collect())->pluck('id')->values();
        });

        $fuelEntries->each(function ($f) {
            $f->receipt_url = \App\Support\FileUrl::for($f->receipt_photo_path);
            $f->linked_journey_id = $f->journey_id;
            $f->linked_journey_date = $f->journey?->start_time?->format('M j, Y');
        });

        return [
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'license_number' => $driver->license_number,
                'license_expiry_date' => $driver->license_expiry_date,
                'license_expiring_soon' => $driver->isLicenseExpiringSoon(),
            ],
            'period' => ['from' => $from, 'to' => $to],
            'total_journeys' => $journeys->count(),
            'total_distance' => (float) $totalDistance,
            'total_fuel_litres' => (float) $totalFuelLitres,
            'total_fuel_cost' => (float) $totalFuelCost,
            'avg_kmpl' => $avgKmpl,
            'vehicles_used' => $vehicleBreakdown,
            'journeys' => $journeys,
            'fuel_entries' => $fuelEntries,
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

        $perVehicle = $entries->groupBy(fn ($e) => $e->vehicle?->registration_number ?? 'Unknown')
            ->map(function ($group, $vehicleName) {
                $totalLitres = $group->sum('quantity_litres');
                $totalCost = $group->sum('total_cost');
                return [
                    'vehicle' => $vehicleName,
                    'entries' => $group->count(),
                    'total_litres' => (float) $totalLitres,
                    'total_cost' => (float) $totalCost,
                    'avg_rate' => $totalLitres > 0 ? round($totalCost / $totalLitres, 2) : null,
                ];
            })->values();

        $totalLitres = $entries->sum('quantity_litres');
        $totalCost = $entries->sum('total_cost');

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total_litres' => (float) $totalLitres,
            'total_cost' => (float) $totalCost,
            'avg_rate' => $totalLitres > 0 ? round($totalCost / $totalLitres, 2) : null,
            'per_vehicle' => $perVehicle,
            'entries' => $entries,
        ];
    }

    public function maintenanceReport(string $from, string $to, ?int $vehicleId = null): array
    {
        $query = MaintenanceRecord::query()
            ->whereBetween('service_date', [$from, $to])
            ->with('vehicle:id,registration_number,current_odometer');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $records = $query->orderBy('service_date')->get();

        $records->each(function ($r) {
            $dateOverdue = $r->next_service_date && $r->next_service_date->isPast();
            $kmOverdue = $r->next_service_km && $r->vehicle && $r->vehicle->current_odometer >= $r->next_service_km;

            $dateSoon = $r->next_service_date && $r->next_service_date->isBefore(now()->addDays(7));
            $kmSoon = $r->next_service_km && $r->vehicle && ($r->next_service_km - $r->vehicle->current_odometer) <= 500;

            $r->due_status = ($dateOverdue || $kmOverdue) ? 'overdue' : (($dateSoon || $kmSoon) ? 'due_soon' : (($r->next_service_date || $r->next_service_km) ? 'ok' : 'none'));
            $r->vehicle_registration = $r->vehicle?->registration_number ?? '-';
        });

        $costByType = $records->groupBy('type')->map(fn ($group, $type) => [
            'type' => $type,
            'count' => $group->count(),
            'total_cost' => (float) $group->sum('cost'),
        ])->values();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total_cost' => (float) $records->sum('cost'),
            'total_records' => $records->count(),
            'overdue_count' => $records->where('due_status', 'overdue')->count(),
            'due_soon_count' => $records->where('due_status', 'due_soon')->count(),
            'cost_by_type' => $costByType,
            'records' => $records,
        ];
    }

    /**
     * Every number here is scoped to the selected calendar month EXCEPT
     * vehicles_overdue_maintenance / vehicles_due_soon_maintenance, which
     * are deliberately fleet-wide and "as of right now" — overdue status
     * is a today question ("does anything need attention right now"), not
     * something that resets at the start of a new month.
     */
    public function fleetSummary(string $month): array
    {
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $journeysInMonth = \App\Models\Journey::where('status', 'completed')->whereBetween('start_time', [$start, $end]);
        $fuelInMonth = FuelEntry::whereBetween('entry_time', [$start, $end]);
        $maintenanceInMonth = MaintenanceRecord::whereBetween('service_date', [$start->toDateString(), $end->toDateString()]);

        $totalDistance = (clone $journeysInMonth)->sum('total_distance');
        $totalFuelLitres = (clone $fuelInMonth)->sum('quantity_litres');
        $totalFuelCost = (clone $fuelInMonth)->sum('total_cost');
        $totalMaintenanceCost = (clone $maintenanceInMonth)->sum('cost');
        $totalJourneys = (clone $journeysInMonth)->count();

        $fleetAvgKmpl = $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null;

        $allMaintenanceRecords = MaintenanceRecord::whereNotNull('next_service_date')
            ->orWhereNotNull('next_service_km')
            ->with('vehicle:id,current_odometer')
            ->get();

        $overdueCount = 0;
        $dueSoonCount = 0;

        foreach ($allMaintenanceRecords as $r) {
            $dateOverdue = $r->next_service_date && $r->next_service_date->isPast();
            $kmOverdue = $r->next_service_km && $r->vehicle && $r->vehicle->current_odometer >= $r->next_service_km;

            if ($dateOverdue || $kmOverdue) {
                $overdueCount++;
                continue;
            }

            $dateSoon = $r->next_service_date && $r->next_service_date->isBefore(now()->addDays(7));
            $kmSoon = $r->next_service_km && $r->vehicle && ($r->next_service_km - $r->vehicle->current_odometer) <= 500;

            if ($dateSoon || $kmSoon) {
                $dueSoonCount++;
            }
        }

        // Per-vehicle: distance, fuel litres AND fuel cost, trip count, and
        // maintenance cost — all scoped to the same month as the fleet
        // totals above, so every row's numbers are directly comparable to
        // the top-line summary and to each other.
        $perVehicle = Vehicle::withCount(['journeys as trip_count' => fn ($q) =>
                $q->where('status', 'completed')->whereBetween('start_time', [$start, $end]),
            ])
            ->withSum(['journeys as month_distance' => fn ($q) =>
                $q->where('status', 'completed')->whereBetween('start_time', [$start, $end]),
            ], 'total_distance')
            ->withSum(['fuelEntries as month_fuel_litres' => fn ($q) =>
                $q->whereBetween('entry_time', [$start, $end]),
            ], 'quantity_litres')
            ->withSum(['fuelEntries as month_fuel_cost' => fn ($q) =>
                $q->whereBetween('entry_time', [$start, $end]),
            ], 'total_cost')
            ->withSum(['maintenanceRecords as month_maintenance_cost' => fn ($q) =>
                $q->whereBetween('service_date', [$start->toDateString(), $end->toDateString()]),
            ], 'cost')
            ->get(['id', 'registration_number'])
            ->map(function ($v) {
                $distance = (float) ($v->month_distance ?? 0);
                $fuelLitres = (float) ($v->month_fuel_litres ?? 0);

                return [
                    'vehicle' => $v->registration_number,
                    'trips' => $v->trip_count,
                    'distance' => $distance,
                    'fuel_litres' => $fuelLitres,
                    'fuel_cost' => (float) ($v->month_fuel_cost ?? 0),
                    'kmpl' => $fuelLitres > 0 ? round($distance / $fuelLitres, 2) : null,
                    'maintenance_cost' => (float) ($v->month_maintenance_cost ?? 0),
                ];
            })
            // Vehicles with zero activity this month sort to the bottom —
            // the ones that actually moved are what a reader wants to see first.
            ->sortByDesc('distance')
            ->values();

        return [
            'month' => $start->format('Y-m'),
            'period_label' => $start->format('F Y'),
            'vehicles' => [
                'total' => Vehicle::count(),
                'active' => Vehicle::where('status', 'active')->count(),
                'inactive' => Vehicle::where('status', 'inactive')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            ],
            'total_journeys' => $totalJourneys,
            'total_distance' => (float) $totalDistance,
            'total_fuel_litres' => (float) $totalFuelLitres,
            'total_fuel_cost' => (float) $totalFuelCost,
            'fleet_avg_kmpl' => $fleetAvgKmpl,
            'total_maintenance_cost' => (float) $totalMaintenanceCost,
            'vehicles_overdue_maintenance' => $overdueCount,
            'vehicles_due_soon_maintenance' => $dueSoonCount,
            'per_vehicle' => $perVehicle,
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