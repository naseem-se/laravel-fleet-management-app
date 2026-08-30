<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(protected VehicleService $vehicles)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = $this->vehicles->paginate(
            $request->only(['status', 'search']),
            (int) $request->input('per_page', 20)
        );

        return VehicleResource::collection($vehicles);
    }

    public function store(StoreVehicleRequest $request)
    {
        $vehicle = $this->vehicles->create($request->user()->company, $request->validated());

        return (new VehicleResource($vehicle))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        return new VehicleResource($vehicle->load('assignedDriver'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        // $this->authorize('update',$vehicle);

        $vehicle = $this->vehicles->update($vehicle, $request->validated());

        return new VehicleResource($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        $this->vehicles->delete($vehicle);

        return response()->json(['message' => 'Vehicle deleted.']);
    }

    public function history(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'journeys' => fn ($q) => $q->with('driver')->latest('start_time')->limit(50),
            'fuelEntries' => fn ($q) => $q->latest('entry_time')->limit(50),
            'maintenanceRecords' => fn ($q) => $q->latest('service_date')->limit(50),
            'documents',
        ]);

        $totalDistance = (float) $vehicle->journeys()
            ->where('status', 'completed')
            ->sum('total_distance');

        $totalFuelLitres = (float) $vehicle->fuelEntries()->sum('quantity_litres');
        $totalFuelCost = (float) $vehicle->fuelEntries()->sum('total_cost');

        $avgKmpl = $totalFuelLitres > 0 ? round($totalDistance / $totalFuelLitres, 2) : null;

        return response()->json([
            'vehicle' => new VehicleResource($vehicle),
            'journeys' => \App\Http\Resources\JourneyResource::collection($vehicle->journeys),
            'fuel_entries' => \App\Http\Resources\FuelEntryResource::collection($vehicle->fuelEntries),
            'maintenance_records' => \App\Http\Resources\MaintenanceRecordResource::collection($vehicle->maintenanceRecords),
            'documents' => \App\Http\Resources\VehicleDocumentResource::collection($vehicle->documents),
            'total_distance' => $totalDistance,
            'total_fuel_litres' => $totalFuelLitres,
            'total_fuel_cost' => $totalFuelCost,
            'avg_kmpl' => $avgKmpl,
        ]);
    }
}