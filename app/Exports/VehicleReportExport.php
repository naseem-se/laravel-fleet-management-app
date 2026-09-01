<?php

namespace App\Exports;

use App\Exports\Sheets\VehicleFuelSheetExport;
use App\Exports\Sheets\VehicleJourneysSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VehicleReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new VehicleJourneysSheetExport($this->data['journeys'], $this->data['fuel_entries']),
            new VehicleFuelSheetExport($this->data['fuel_entries'], $this->data['journeys']),
        ];
    }
}