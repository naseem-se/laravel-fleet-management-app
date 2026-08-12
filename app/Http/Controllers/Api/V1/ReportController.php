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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports)
    {
    }

    public function overview()
    {
        $this->authorize('viewAny', Vehicle::class);

        return $this->safely(fn () => response()->json($this->reports->dashboardOverview()), 'dashboard overview');
    }

    public function vehicle(Request $request, Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        return $this->safely(function () use ($request, $vehicle) {
            [$from, $to] = $this->period($request);
            $data = $this->reports->vehicleReport($vehicle, $from, $to);

            return $this->respond($request, $data, 'reports.vehicle',
                fn () => new VehicleJourneysExport($data['journeys']),
                "vehicle-{$vehicle->registration_number}-report"
            );
        }, 'vehicle report');
    }

    public function driver(Request $request, Driver $driver)
    {
        $this->authorize('view', $driver);

        return $this->safely(function () use ($request, $driver) {
            [$from, $to] = $this->period($request);

            return response()->json($this->reports->driverReport($driver, $from, $to));
        }, 'driver report');
    }

    public function fuel(Request $request)
    {
        $this->authorize('viewAny', \App\Models\FuelEntry::class);

        return $this->safely(function () use ($request) {
            // Was: fuelReport(...$this->period($request), $request->input('vehicle_id'))
            // — a positional argument after array unpacking is a PHP fatal error.
            [$from, $to] = $this->period($request);
            $data = $this->reports->fuelReport($from, $to, $request->input('vehicle_id'));

            if ($request->input('format') === 'xlsx') {
                return Excel::download(new FuelReportExport($data['entries']), 'fuel-report.xlsx');
            }

            return response()->json($data);
        }, 'fuel report');
    }

    public function maintenance(Request $request)
    {
        $this->authorize('viewAny', \App\Models\MaintenanceRecord::class);

        return $this->safely(function () use ($request) {
            [$from, $to] = $this->period($request);
            $data = $this->reports->maintenanceReport($from, $to, $request->input('vehicle_id'));

            if ($request->input('format') === 'xlsx') {
                return Excel::download(new MaintenanceReportExport($data['records']), 'maintenance-report.xlsx');
            }

            return response()->json($data);
        }, 'maintenance report');
    }

    public function fleetSummary(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        return $this->safely(function () use ($request) {
            $month = $request->input('month', now()->format('Y-m'));
            $data = $this->reports->fleetSummary($month);

            if ($request->input('format') === 'pdf') {
                return Pdf::loadView('reports.fleet-summary', ['data' => $data])
                    ->download("fleet-summary-{$month}.pdf");
            }

            if ($request->input('format') === 'xlsx') {
                return Excel::download(new FleetSummaryExport($data['per_vehicle']), "fleet-summary-{$month}.xlsx");
            }

            return response()->json($data);
        }, 'fleet summary');
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

    /** Guarantees a real JSON error response (with CORS headers intact) and a logged stack trace instead of a raw PHP fatal. */
    protected function safely(callable $action, string $context)
    {
        try {
            return $action();
        } catch (Throwable $e) {
            Log::error("Report generation failed: {$context}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => app()->hasDebugModeEnabled()
                    ? "Report generation failed: {$e->getMessage()}"
                    : 'Report generation failed. Please try again or contact support.',
            ], 500);
        }
    }
}