<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fuel\StoreFuelEntryRequest;
use App\Http\Resources\FuelEntryResource;
use App\Models\FuelEntry;
use App\Services\FuelService;
use Illuminate\Http\Request;

class FuelEntryController extends Controller
{
    public function __construct(protected FuelService $fuel)
    {
    }

    public function store(StoreFuelEntryRequest $request)
    {
        $entry = $this->fuel->create($request->user()->driver, $request->validated());

        return (new FuelEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', FuelEntry::class);

        $query = FuelEntry::query()->with(['vehicle', 'driver']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('entry_time', [
                $request->input('from').' 00:00:00',
                $request->input('to').' 23:59:59',
            ]);
        }

        return FuelEntryResource::collection(
            $query->latest('entry_time')->paginate((int) $request->input('per_page', 20))
        );
    }
    public function storeAdmin(\App\Http\Requests\Fuel\StoreFuelEntryAdminRequest $request)
    {
        $entry = $this->fuel->createManual($request->user()->company_id, $request->validated());

        return (new FuelEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }
    public function mine(Request $request)
    {
        $entries = $request->user()->driver
            ->fuelEntries()
            ->with('vehicle')
            ->latest('entry_time')
            ->paginate((int) $request->input('per_page', 20));

        return FuelEntryResource::collection($entries);
    }
}