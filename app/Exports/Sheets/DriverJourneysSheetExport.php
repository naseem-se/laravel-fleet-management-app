<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DriverJourneysSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    public function __construct(
        protected Collection $journeys,
        protected Collection $fuelEntries
    ) {
    }

    public function collection(): Collection
    {
        return $this->journeys;
    }

    public function title(): string
    {
        return 'Journeys';
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Purpose', 'Start KM', 'End KM', 'Distance', 'Fuel Logged'];
    }

    public function map($journey): array
    {
        return [
            optional($journey->start_time)->format('Y-m-d h:i A') ?? '-',
            $journey->vehicle_registration,
            $journey->purpose_display,
            $journey->start_km,
            $journey->end_km ?? '-',
            $journey->distance_display,
            '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $fuelRowsById = $this->fuelEntries->values()->mapWithKeys(fn ($f, $i) => [$f->id => $i + 2]);

                foreach ($this->journeys->values() as $index => $journey) {
                    $row = $index + 2;
                    $linkedFuelIds = $journey->linked_fuel_ids ?? collect();

                    if ($linkedFuelIds->isNotEmpty()) {
                        $firstFuelRow = $fuelRowsById[$linkedFuelIds->first()] ?? null;
                        if ($firstFuelRow) {
                            $cell = $sheet->getCell("G{$row}");
                            $cell->setValue($linkedFuelIds->count() > 1 ? 'View Fuel Entries' : 'View Fuel Entry');
                            $cell->getHyperlink()->setUrl("sheet://'Fuel Entries'!A{$firstFuelRow}");
                        }
                    } else {
                        $sheet->getCell("G{$row}")->setValue('None logged');
                    }
                }

                $sheet->getColumnDimension('G')->setWidth(20);
            },
        ];
    }
}