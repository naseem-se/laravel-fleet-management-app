<?php

namespace App\Exports;

use App\Exports\Sheets\FuelEntriesSheetExport;
use App\Exports\Sheets\FuelSummarySheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FuelReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new FuelSummarySheetExport($this->data),
            new FuelEntriesSheetExport($this->data['entries']),
        ];
    }
}