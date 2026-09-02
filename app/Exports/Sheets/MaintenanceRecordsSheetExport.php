<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MaintenanceRecordsSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Collection $records)
    {
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function title(): string
    {
        return 'Records';
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Type', 'Description', 'Cost', 'Performed By', 'Next Due (Date)', 'Next Due (KM)', 'Status'];
    }

    public function map($record): array
    {
        return [
            $record->service_date->format('Y-m-d'),
            $record->vehicle_registration,
            ucfirst(str_replace('_', ' ', $record->type)),
            $record->description ?? '-',
            $record->cost,
            $record->performed_by ?? '-',
            optional($record->next_service_date)->format('Y-m-d') ?? '-',
            $record->next_service_km ?? '-',
            ucfirst(str_replace('_', ' ', $record->due_status)),
        ];
    }
}