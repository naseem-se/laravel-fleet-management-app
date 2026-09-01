<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class VehicleJourneysExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
            'Date', 'Time From', 'Time To', 'Driver', 'Detail of Journey', 'Purpose of Journey',
            'Officer/Official', 'Meter From', 'Meter To', 'KM Covered', 'Signature',
            'Start Photo', 'End Photo',
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
            '',
            '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $disk = Storage::disk(config('filesystems.default'));

                foreach ($this->journeys->values() as $index => $journey) {
                    $row = $index + 2;
                    $this->drawThumbnail($event, $disk, $journey->start_photo_path, "L{$row}");
                    $this->drawThumbnail($event, $disk, $journey->end_photo_path, "M{$row}");
                    $event->sheet->getDelegate()->getRowDimension($row)->setRowHeight(55);
                }

                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth(12);
            },
        ];
    }

    protected function drawThumbnail(AfterSheet $event, $disk, ?string $path, string $cell): void
    {
        if (! $path || ! $disk->exists($path)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($disk->path($path));
        $drawing->setHeight(55);
        $drawing->setCoordinates($cell);
        $drawing->setWorksheet($event->sheet->getDelegate());
    }
}