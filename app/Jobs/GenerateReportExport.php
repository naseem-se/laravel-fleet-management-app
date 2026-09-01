<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ReportReadyNotification;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300; // 5 min — large exports shouldn't hang a worker forever

    public function __construct(
        protected int $userId,
        protected int $companyId,
        protected string $reportType, // 'fleet-summary' | 'fuel' | 'maintenance'
        protected string $format,     // 'pdf' | 'xlsx'
        protected array $params,
    ) {
        $this->onQueue('reports');
    }

    public function handle(ReportService $reports): void
    {
        $user = User::withoutGlobalScopes()->find($this->userId);
        if (! $user) return;

        // Force the tenant scope explicitly since this runs outside an
        // authenticated HTTP request — no Auth::user() to derive it from.
        $filePath = match ($this->reportType) {
            'fleet-summary' => $this->generateFleetSummary($reports),
            'fuel' => $this->generateFuelReport($reports),
            'maintenance' => $this->generateMaintenanceReport($reports),
            default => null,
        };

        if ($filePath) {
            $user->notify(new ReportReadyNotification($filePath, $this->reportType));
        }
    }

    protected function generateFleetSummary(ReportService $reports): string
    {
        $month = $this->params['month'] ?? now()->format('Y-m');
        $data = $reports->fleetSummary($month);

        $filename = "reports/{$this->companyId}/fleet-summary-{$month}-".uniqid().".{$this->format}";

        if ($this->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.fleet-summary', ['data' => $data]);
            Storage::put($filename, $pdf->output());
        } else {
            \Maatwebsite\Excel\Facades\Excel::store(
                new \App\Exports\FleetSummaryExport($data['per_vehicle']),
                $filename
            );
        }

        return $filename;
    }

    protected function generateFuelReport(ReportService $reports): string
    {
        $data = $reports->fuelReport($this->params['from'], $this->params['to'], $this->params['vehicle_id'] ?? null);
        $filename = "reports/{$this->companyId}/fuel-report-".uniqid().'.xlsx';

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\FuelReportExport($data['entries']), $filename);

        return $filename;
    }

    protected function generateMaintenanceReport(ReportService $reports): string
    {
        $data = $reports->maintenanceReport($this->params['from'], $this->params['to'], $this->params['vehicle_id'] ?? null);
        $filename = "reports/{$this->companyId}/maintenance-report-".uniqid().'.xlsx';

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\MaintenanceReportExport($data['records']), $filename);

        return $filename;
    }
}