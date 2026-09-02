<?php

namespace App\Exports;

use App\Exports\Sheets\MaintenanceRecordsSheetExport;
use App\Exports\Sheets\MaintenanceSummarySheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MaintenanceReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new MaintenanceSummarySheetExport($this->data),
            new MaintenanceRecordsSheetExport($this->data['records']),
        ];
    }
}