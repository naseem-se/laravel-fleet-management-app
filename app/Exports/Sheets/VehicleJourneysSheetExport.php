<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VehicleJourneysSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
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
        return 'Journey Log';
    }

    public function headings(): array
    {
        return [
            'Date', 'Time From', 'Time To', 'Driver', 'Detail of Journey', 'Purpose of Journey',
            'Officer/Official', 'Meter From', 'Meter To', 'KM Covered', 'Signature',
            'Start Photo', 'End Photo', 'Fuel Logged',
        ];
    }

    public function map($journey): array
    {
        return [
            optional($journey->start_time)->format('Y-m-d') ?? '-',
            $journey->start_time ? $journey->start_time->format('h:i A') : '-',
            $journey->end_time ? $journey->end_time->format('h:i A') : '-',
            $journey->driver_name,
            $journey->detail_display,
            $journey->purpose_display,
            $journey->officer_display,
            $journey->start_km_display,
            $journey->end_km_display,
            $journey->distance_display,
            $journey->signature_display,
            $journey->start_photo_url ? 'View Photo' : 'No photo',
            $journey->end_photo_url ? 'View Photo' : 'No photo',
            '', // filled in below — a real clickable link to the Fuel Purchases sheet
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

                    if ($journey->start_photo_url) {
                        $sheet->getCell("L{$row}")->getHyperlink()->setUrl($journey->start_photo_url);
                    }
                    if ($journey->end_photo_url) {
                        $sheet->getCell("M{$row}")->getHyperlink()->setUrl($journey->end_photo_url);
                    }

                    $linkedFuelIds = $journey->linked_fuel_ids ?? collect();
                    if ($linkedFuelIds->isNotEmpty()) {
                        $firstFuelRow = $fuelRowsById[$linkedFuelIds->first()] ?? null;
                        if ($firstFuelRow) {
                            $cell = $sheet->getCell("N{$row}");
                            $label = $linkedFuelIds->count() > 1
                                ? 'View Fuel Entries ('.$linkedFuelIds->count().')'
                                : 'View Fuel Entry';
                            $cell->setValue($label);
                            $cell->getHyperlink()->setUrl("sheet://'Fuel Purchases'!A{$firstFuelRow}");
                        }
                    } else {
                        $sheet->getCell("N{$row}")->setValue('None logged');
                    }
                }

                foreach (['L', 'M', 'N'] as $col) {
                    $sheet->getColumnDimension($col)->setWidth(20);
                }
            },
        ];
    }
}