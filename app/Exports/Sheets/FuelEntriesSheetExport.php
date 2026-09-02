<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FuelEntriesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    public function __construct(protected Collection $entries)
    {
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function title(): string
    {
        return 'Entries';
    }

    public function headings(): array
    {
        return ['Date', 'Vehicle', 'Driver', 'Litres', 'Rate/Litre', 'Total Cost', 'Odometer', 'Linked Trip Date', 'Receipt'];
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
            $entry->linked_journey_date ?? '-',
            $entry->receipt_url ? 'View Receipt' : 'No receipt',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                foreach ($this->entries->values() as $index => $entry) {
                    if ($entry->receipt_url) {
                        $sheet->getCell('I'.($index + 2))->getHyperlink()->setUrl($entry->receipt_url);
                    }
                }
                $sheet->getColumnDimension('I')->setWidth(16);
            },
        ];
    }
}