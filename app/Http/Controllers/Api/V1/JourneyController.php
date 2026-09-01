<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\EndJourneyRequest;
use App\Http\Requests\Journey\PingJourneyRequest;
use App\Http\Requests\Journey\StartJourneyRequest;
use App\Http\Requests\Journey\UpdateJourneyRequest;
use App\Http\Resources\JourneyResource;
use App\Models\Journey;
use App\Services\JourneyService;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    public function __construct(protected JourneyService $journeys)
    {
    }

    public function start(StartJourneyRequest $request)
    {
        $journey = $this->journeys->start($request->user()->driver, $request->validated());

        return (new JourneyResource($journey->load(['vehicle', 'driver'])))
            ->response()
            ->setStatusCode(201);
    }

    public function ping(PingJourneyRequest $request, Journey $journey)
    {
        $this->journeys->ping($journey, $request->validated());

        return response()->json(['message' => 'Location recorded.']);
    }

    public function end(EndJourneyRequest $request, Journey $journey)
    {
        $journey = $this->journeys->end($journey, $request->validated());

        return new JourneyResource($journey->load(['vehicle', 'driver', 'fuelEntries']));
    }

    public function update(UpdateJourneyRequest $request, Journey $journey)
    {
        $journey = $this->journeys->updateDetails($journey, $request->validated());

        return new JourneyResource($journey);
    }

    public function destroy(Journey $journey)
    {
        $this->authorize('delete', $journey);

        $this->journeys->delete($journey);

        return response()->json(['message' => 'Journey deleted.']);
    }

    public function current(Request $request)
    {
        $journey = $request->user()->driver
            ->journeys()
            ->where('status', 'active')
            ->with(['vehicle', 'driver'])
            ->first();

        return $journey
            ? new JourneyResource($journey)
            : response()->json(['data' => null]);
    }

    public function live()
    {
        $this->authorize('viewAny', Journey::class);

        $journeys = Journey::where('status', 'active')
            ->with(['vehicle', 'driver'])
            ->get();

        return JourneyResource::collection($journeys);
    }

    public function show(Journey $journey)
    {
        $this->authorize('view', $journey);

        return new JourneyResource($journey->load(['vehicle', 'driver', 'fuelEntries']));
    }

    public function vehicleDocuments(Journey $journey)
    {
        $this->authorize('view', $journey);

        return \App\Http\Resources\VehicleDocumentResource::collection(
            $journey->vehicle->documents
        );
    }
}