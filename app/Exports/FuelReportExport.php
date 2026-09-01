<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FuelReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $entries)
    {
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Driver', 'Litres', 'Rate/Litre', 'Total Cost', 'Odometer', 'Linked Trip Date'];
    }

    public function map($entry): array
    {
        return [
            $entry->entry_time->format('Y-m-d h:i A'),
            $entry->vehicle->registration_number ?? '-',
            $entry->driver->name ?? '-',
            $entry->quantity_litres,
            $entry->rate_per_litre,
            $entry->total_cost,
            $entry->odometer_reading,
            $entry->journey?->start_time?->format('Y-m-d') ?? '-',
        ];
    }
}