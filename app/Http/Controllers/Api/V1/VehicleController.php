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

    /**
     * Full history for the vehicle detail page: journeys, fuel, maintenance,
     * combined and ordered newest-first. Kept here rather than in the
     * service since it's purely a read/presentation concern.
     */
    public function history(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'assignedDriver', // was missing — this is why the detail page showed no assigned driver
            'journeys' => fn ($q) => $q->latest('start_time')->limit(50),
            'fuelEntries' => fn ($q) => $q->latest('entry_time')->limit(50),
            'maintenanceRecords' => fn ($q) => $q->latest('service_date')->limit(50),
            'documents',
        ]);

        return response()->json([
            'vehicle' => new VehicleResource($vehicle),
            'journeys' => $vehicle->journeys,
            'fuel_entries' => $vehicle->fuelEntries,
            'maintenance_records' => $vehicle->maintenanceRecords,
            'documents' => $vehicle->documents,
        ]);
    }
}