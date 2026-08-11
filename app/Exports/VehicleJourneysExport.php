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
        return ['Start Time', 'End Time', 'Start KM', 'End KM', 'Distance (KM)'];
    }

    public function map($journey): array
    {
        return [
            optional($journey->start_time)->format('Y-m-d H:i'),
            optional($journey->end_time)->format('Y-m-d H:i'),
            $journey->start_km,
            $journey->end_km,
            $journey->total_distance,
        ];
    }
}