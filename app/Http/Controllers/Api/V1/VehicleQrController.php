<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleQrController extends Controller
{
    /**
     * Resolves a scanned QR token to a vehicle. The token is a random UUID
     * (see Vehicle::booted()), never the plate number, so this endpoint
     * can't be used to enumerate vehicles by guessing plates.
     */
    public function resolve(string $qrToken, Request $request)
    {
        $vehicle = Vehicle::where('qr_code_value', $qrToken)->firstOrFail();

        $this->authorize('view', $vehicle);

        return new VehicleResource($vehicle->load('assignedDriver'));
    }
}