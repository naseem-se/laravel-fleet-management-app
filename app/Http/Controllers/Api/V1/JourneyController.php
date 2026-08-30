<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\EndJourneyRequest;
use App\Http\Requests\Journey\PingJourneyRequest;
use App\Http\Requests\Journey\StartJourneyRequest;
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

        return new JourneyResource($journey->load(['vehicle', 'driver']));
    }

    /** The logged-in driver's own in-progress journey, if any. */
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

    /** Admin/dispatcher live map — every active journey across the company. */
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

        return new JourneyResource($journey->load(['vehicle', 'driver', 'fuelEntries', 'locations', 'locationSummary']));
    }

    public function destroy(Journey $journey)
    {
        $this->authorize('delete', $journey);

        $this->journeys->delete($journey);

        return response()->json(['message' => 'Journey deleted.']);
    }

    public function vehicleDocuments(Journey $journey)
    {
        $this->authorize('view', $journey); // own journey only, or admin/dispatcher — same rule as viewing the journey itself

        return \App\Http\Resources\VehicleDocumentResource::collection(
            $journey->vehicle->documents
        );
    }
    
}