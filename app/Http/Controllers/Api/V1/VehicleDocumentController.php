<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleDocument\StoreVehicleDocumentRequest;
use App\Http\Requests\VehicleDocument\UpdateVehicleDocumentRequest;
use App\Http\Resources\VehicleDocumentResource;
use App\Models\VehicleDocument;
use App\Services\VehicleDocumentService;
use Illuminate\Http\Request;

class VehicleDocumentController extends Controller
{
    public function __construct(protected VehicleDocumentService $documents)
    {
    }

    public function store(StoreVehicleDocumentRequest $request)
    {
        $document = $this->documents->create($request->user()->company_id, $request->validated());

        return (new VehicleDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateVehicleDocumentRequest $request, VehicleDocument $vehicleDocument)
    {
        $document = $this->documents->update($vehicleDocument, $request->validated());

        return new VehicleDocumentResource($document);
    }

    public function destroy(VehicleDocument $vehicleDocument)
    {
        $this->authorize('delete', $vehicleDocument);

        $this->documents->delete($vehicleDocument);

        return response()->json(['message' => 'Document deleted.']);
    }

    public function expiring(Request $request)
    {
        $this->authorize('viewAny', VehicleDocument::class);

        $withinDays = (int) $request->input('days', 30);

        $documents = VehicleDocument::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($withinDays))
            ->with('vehicle')
            ->orderBy('expiry_date')
            ->get();

        return VehicleDocumentResource::collection($documents);
    }
}