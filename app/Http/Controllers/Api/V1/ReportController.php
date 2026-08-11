<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\FleetSummaryExport;
use App\Exports\FuelReportExport;
use App\Exports\MaintenanceReportExport;
use App\Exports\VehicleJourneysExport;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports)
    {
    }

    public function vehicle(Request $request, Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $data = $this->reports->vehicleReport($vehicle, ...$this->period($request));

        return $this->respond($request, $data, 'reports.vehicle',
            fn () => new VehicleJourneysExport($data['journeys']),
            "vehicle-{$vehicle->registration_number}-report"
        );
    }

    public function driver(Request $request, Driver $driver)
    {
        $this->authorize('view', $driver);

        $data = $this->reports->driverReport($driver, ...$this->period($request));

        return response()->json($data); // no PDF/Excel view built for this one yet — JSON only, extend the same pattern as vehicle() if needed
    }

    public function fuel(Request $request)
    {
        $this->authorize('viewAny', \App\Models\FuelEntry::class);

        $data = $this->reports->fuelReport(...$this->period($request), $request->input('vehicle_id'));

        if ($request->input('format') === 'xlsx') {
            return Excel::download(new FuelReportExport($data['entries']), 'fuel-report.xlsx');
        }

        return response()->json($data);
    }

    public function maintenance(Request $request)
    {
        $this->authorize('viewAny', \App\Models\MaintenanceRecord::class);

        $data = $this->reports->maintenanceReport(...$this->period($request), $request->input('vehicle_id'));

        if ($request->input('format') === 'xlsx') {
            return Excel::download(new MaintenanceReportExport($data['records']), 'maintenance-report.xlsx');
        }

        return response()->json($data);
    }

    public function fleetSummary(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $month = $request->input('month', now()->format('Y-m'));
        $data = $this->reports->fleetSummary($month);

        if ($request->input('format') === 'pdf') {
            return Pdf::loadView('reports.fleet-summary', ['data' => $data])
                ->setPaper('a4', 'landscape')
                ->download("fleet-summary-{$month}.pdf");
        }

        if ($request->input('format') === 'xlsx') {
            return Excel::download(new FleetSummaryExport($data['per_vehicle']), "fleet-summary-{$month}.xlsx");
        }

        return response()->json($data);
    }

    protected function period(Request $request): array
    {
        return [
            $request->input('from', now()->subDays(30)->toDateString()),
            $request->input('to', now()->toDateString()),
        ];
    }

    protected function respond(Request $request, array $data, string $view, callable $exportFactory, string $filename)
    {
        return match ($request->input('format')) {
            'pdf' => Pdf::loadView($view, ['data' => $data])->download("{$filename}.pdf"),
            'xlsx' => Excel::download($exportFactory(), "{$filename}.xlsx"),
            default => response()->json($data),
        };
    }
}