<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreMaintenanceRecordRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRecordRequest;
use App\Http\Resources\MaintenanceRecordResource;
use App\Models\MaintenanceRecord;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;

class MaintenanceRecordController extends Controller
{
    public function __construct(protected MaintenanceService $maintenance)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', MaintenanceRecord::class);

        $records = $this->maintenance->paginate(
            $request->only(['vehicle_id', 'type']),
            (int) $request->input('per_page', 20)
        );

        return MaintenanceRecordResource::collection($records);
    }

    public function store(StoreMaintenanceRecordRequest $request)
    {
        $record = $this->maintenance->create($request->validated());

        return (new MaintenanceRecordResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MaintenanceRecord $maintenanceRecord)
    {
        $this->authorize('view', $maintenanceRecord);

        return new MaintenanceRecordResource($maintenanceRecord);
    }

    public function update(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintenanceRecord)
    {
        $record = $this->maintenance->update($maintenanceRecord, $request->validated());

        return new MaintenanceRecordResource($record);
    }

    public function destroy(MaintenanceRecord $maintenanceRecord)
    {
        $this->authorize('delete', $maintenanceRecord);

        $this->maintenance->delete($maintenanceRecord);

        return response()->json(['message' => 'Maintenance record deleted.']);
    }

    /** Fleet-wide upcoming maintenance, for the dashboard alerts widget. */
    public function upcoming(Request $request)
    {
        $this->authorize('viewAny', MaintenanceRecord::class);

        $withinDays = (int) $request->input('days', 30);

        $records = MaintenanceRecord::whereNotNull('next_service_date')
            ->whereDate('next_service_date', '<=', now()->addDays($withinDays))
            ->with('vehicle')
            ->orderBy('next_service_date')
            ->get();

        return MaintenanceRecordResource::collection($records);
    }
}