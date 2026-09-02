<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DriverFuelSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
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
        return 'Fuel Entries';
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Litres', 'Rate/Litre', 'Total Cost', 'Linked Trip', 'Receipt'];
    }

    public function map($entry): array
    {
        return [
            optional($entry->entry_time)->format('Y-m-d h:i A') ?? '-',
            $entry->vehicle->registration_number ?? '-',
            $entry->quantity_litres,
            $entry->rate_per_litre,
            $entry->total_cost,
            '',
            $entry->receipt_url ? 'View Receipt' : 'No receipt',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $journeyRowsById = $this->journeys->values()->mapWithKeys(fn ($j, $i) => [$j->id => $i + 2]);

                foreach ($this->fuelEntries->values() as $index => $entry) {
                    $row = $index + 2;

                    if ($entry->receipt_url) {
                        $sheet->getCell("G{$row}")->getHyperlink()->setUrl($entry->receipt_url);
                    }

                    if ($entry->linked_journey_id && isset($journeyRowsById[$entry->linked_journey_id])) {
                        $journeyRow = $journeyRowsById[$entry->linked_journey_id];
                        $cell = $sheet->getCell("F{$row}");
                        $cell->setValue('View Trip ('.$entry->linked_journey_date.')');
                        $cell->getHyperlink()->setUrl("sheet://'Journeys'!A{$journeyRow}");
                    } else {
                        $sheet->getCell("F{$row}")->setValue('Not linked');
                    }
                }

                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(16);
            },
        ];
    }
}