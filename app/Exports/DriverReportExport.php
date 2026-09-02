<?php

namespace App\Exports;

use App\Exports\Sheets\DriverFuelSheetExport;
use App\Exports\Sheets\DriverJourneysSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DriverReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new DriverJourneysSheetExport($this->data['journeys'], $this->data['fuel_entries']),
            new DriverFuelSheetExport($this->data['fuel_entries'], $this->data['journeys']),
        ];
    }
}