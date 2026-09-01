<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VehicleFuelSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    public function __construct(
        protected Collection $fuelEntries,
        protected Collection $journeys
    ) {
    }

    public function collection(): Collection
    {
        return $this->fuelEntries;
    }

    public function title(): string
    {
        return 'Fuel Purchases';
    }

    public function headings(): array
    {
        return [
            'Date', 'Driver', 'Litres', 'Rate/Litre', 'Total Cost',
            'Odometer at Purchase', 'Linked Trip', 'Receipt',
        ];
    }

    public function map($entry): array
    {
        return [
            optional($entry->entry_time)->format('Y-m-d h:i A') ?? '-',
            $entry->driver?->name ?? '-',
            $entry->quantity_litres,
            $entry->rate_per_litre,
            $entry->total_cost,
            $entry->odometer_reading,
            '', // Linked Trip — hyperlink set below
            $entry->receipt_url ? 'View Receipt' : 'No receipt',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($this->fuelEntries->values() as $index => $entry) {
                    $row = $index + 2;

                    if ($entry->receipt_url) {
                        $sheet->getCell("H{$row}")->getHyperlink()->setUrl($entry->receipt_url);
                    }

                    if ($entry->linked_journey_id) {
                        $journeyRowIndex = $this->journeys->values()->search(fn ($j) => $j->id === $entry->linked_journey_id);

                        if ($journeyRowIndex !== false) {
                            $journeyRow = $journeyRowIndex + 2;
                            $cell = $sheet->getCell("G{$row}");
                            $cell->setValue('View Trip ('.$entry->linked_journey_date.')');
                            $cell->getHyperlink()->setUrl("sheet://'Journey Log'!A{$journeyRow}");
                        }
                    } else {
                        $sheet->getCell("G{$row}")->setValue('Not linked to a trip');
                    }
                }

                $sheet->getColumnDimension('G')->setWidth(24);
                $sheet->getColumnDimension('H')->setWidth(16);
            },
        ];
    }
}