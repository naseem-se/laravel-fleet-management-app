<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleJourneysExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $journeys)
    {
    }

    public function collection(): Collection
    {
        return $this->journeys;
    }

    public function headings(): array
    {
        return [
            'Date', 'Time From', 'Time To', 'Detail of Journey', 'Purpose of Journey',
            'Name of Officer/Official', 'Meter Reading From', 'Meter Reading To',
            'KM Covered', 'Signature', 'P.O.L. Drawn', 'Remarks',
        ];
    }

    public function map($journey): array
    {
        return [
            optional($journey->start_time)->format('Y-m-d'),
            optional($journey->start_time)->format('H:i'),
            optional($journey->end_time)->format('H:i'),
            $journey->detail_of_journey ?? '',
            $journey->purpose ?? '',
            $journey->officer_name ?? $journey->driver_name ?? '',
            $journey->start_km,
            $journey->end_km,
            $journey->total_distance,
            $journey->signature ?? '',
            $journey->pol_drawn ?? '',
            $journey->remarks ?? '',
        ];
    }
}