<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FleetSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $perVehicle)
    {
    }

    public function collection(): Collection
    {
        return $this->perVehicle;
    }

    public function headings(): array
    {
        return ['Vehicle', 'Distance (KM)', 'Fuel (Litres)', 'KMPL'];
    }

    public function map($row): array
    {
        return [$row['vehicle'], $row['distance'], $row['fuel_litres'], $row['kmpl']];
    }
}