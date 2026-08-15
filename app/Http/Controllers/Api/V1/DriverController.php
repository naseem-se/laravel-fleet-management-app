<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(protected DriverService $drivers)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Driver::class);

        $drivers = $this->drivers->paginate(
            $request->only(['status', 'search']),
            (int) $request->input('per_page', 20)
        );

        return DriverResource::collection($drivers);
    }

    public function store(StoreDriverRequest $request)
    {
        $driver = $this->drivers->create($request->user()->company, $request->validated());

        return (new DriverResource($driver))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Driver $driver)
    {
        $this->authorize('view', $driver);

        return new DriverResource($driver->load(['assignedVehicle', 'documents']));
    }

    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $driver = $this->drivers->update($driver, $request->validated());

        return new DriverResource($driver);
    }

    public function destroy(Driver $driver)
    {
        $this->authorize('delete', $driver);

        $this->drivers->delete($driver);

        return response()->json(['message' => 'Driver deleted.']);
    }

    /**
     * Simple performance snapshot — journey count, total distance, total
     * fuel cost for a period. Report-module (Step 9) will build richer
     * exports on top of the same underlying queries.
     */
    public function performance(Driver $driver, Request $request)
    {
        $this->authorize('view', $driver);

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $journeys = $driver->journeys()
            ->whereBetween('start_time', ["{$from} 00:00:00", "{$to} 23:59:59"]);

        $fuel = $driver->fuelEntries()
            ->whereBetween('entry_time', ["{$from} 00:00:00", "{$to} 23:59:59"]);

        return response()->json([
            'driver' => new DriverResource($driver),
            'period' => ['from' => $from, 'to' => $to],
            'total_journeys' => (clone $journeys)->count(),
            'total_distance' => (clone $journeys)->sum('total_distance'),
            'total_fuel_litres' => (clone $fuel)->sum('quantity_litres'),
            'total_fuel_cost' => (clone $fuel)->sum('total_cost'),
        ]);
    }

    public function createLogin(\App\Http\Requests\Driver\StoreDriverLoginRequest $request, Driver $driver)
    {
        $driver = $this->drivers->createLogin($driver, $request->validated());

        return new DriverResource($driver);
    }
    public function updateLogin(\App\Http\Requests\Driver\UpdateDriverLoginRequest $request, Driver $driver)
    {
        $driver = $this->drivers->updateLogin($driver, $request->validated());

        return new DriverResource($driver);
    }
}