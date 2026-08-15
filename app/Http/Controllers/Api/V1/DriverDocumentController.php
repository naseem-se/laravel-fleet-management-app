<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverDocument\StoreDriverDocumentRequest;
use App\Http\Requests\DriverDocument\UpdateDriverDocumentRequest;
use App\Http\Resources\DriverDocumentResource;
use App\Models\Driver;
use App\Models\DriverDocument;
use App\Services\DriverDocumentService;
use Illuminate\Http\Request;

class DriverDocumentController extends Controller
{
    public function __construct(protected DriverDocumentService $documents)
    {
    }

    public function store(StoreDriverDocumentRequest $request, Driver $driver)
    {
        $document = $this->documents->create($request->user()->company_id, $driver->id, $request->validated());

        return (new DriverDocumentResource($document))->response()->setStatusCode(201);
    }

    public function update(UpdateDriverDocumentRequest $request, DriverDocument $driverDocument)
    {
        $document = $this->documents->update($driverDocument, $request->validated());

        return new DriverDocumentResource($document);
    }

    public function destroy(DriverDocument $driverDocument)
    {
        $this->documents->delete($driverDocument);

        return response()->json(['message' => 'Document deleted.']);
    }

    public function expiring(Request $request)
    {
        $withinDays = (int) $request->input('days', 30);

        $documents = DriverDocument::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($withinDays))
            ->with('driver')
            ->orderBy('expiry_date')
            ->get();

        return DriverDocumentResource::collection($documents);
    }
}