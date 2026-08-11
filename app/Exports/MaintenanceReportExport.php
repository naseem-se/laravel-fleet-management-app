<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaintenanceReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $records)
    {
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Type', 'Description', 'Cost', 'Performed By'];
    }

    public function map($record): array
    {
        return [
            $record->service_date->format('Y-m-d'),
            $record->vehicle->registration_number ?? '-',
            $record->type,
            $record->description,
            $record->cost,
            $record->performed_by,
        ];
    }
}