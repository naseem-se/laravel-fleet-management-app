<?php

namespace App\Exports;

use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithDrawings;
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
            'P.O.L. Drawn', 'Start Photo', 'End Photo', 'P.O.L. Invoice',
        ];
    }

    public function map($journey): array
    {
        return [
            optional($journey->start_time)->format('Y-m-d') ?? '-',
            $journey->start_time ? $journey->start_time->format('h:i A') : '-',
            $journey->end_time_display,
            $journey->driver_name,
            $journey->detail_display,
            $journey->purpose_display,
            $journey->officer_display,
            $journey->start_km_display,
            $journey->end_km_display,
            $journey->distance_display,
            $journey->signature_display,
            $journey->pol_display,
            '', // Start Photo — image drawn over this cell below, left blank as text
            '', // End Photo
            '', // P.O.L. Invoice
        ];
    }

    /**
     * Draws each journey's photos over their designated cells, row by row.
     * Row 1 is the header, so data starts at row 2 — this is why each
     * drawing's coordinate is $index + 2, matching the same order the
     * collection was mapped in above.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $disk = Storage::disk(config('filesystems.default'));

                foreach ($this->journeys->values() as $index => $journey) {
                    $row = $index + 2;

                    $this->drawThumbnail($event, $disk, $journey->start_photo_path, "M{$row}");
                    $this->drawThumbnail($event, $disk, $journey->end_photo_path, "N{$row}");

                    if ($journey->pol_drawn > 0) {
                        $this->drawThumbnail($event, $disk, $journey->pol_invoice_photo_path, "O{$row}");
                    }

                    $event->sheet->getDelegate()->getRowDimension($row)->setRowHeight(60);
                }

                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('N')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('O')->setWidth(12);
            },

            
        ];
    }

    protected function drawThumbnail(AfterSheet $event, $disk, ?string $path, string $cell): void
    {
        if (! $path || ! $disk->exists($path)) {
            return;
        }

        $absolutePath = $disk->path($path);

        $drawing = new Drawing();
        $drawing->setPath($absolutePath);
        $drawing->setHeight(60);
        $drawing->setCoordinates($cell);
        $drawing->setWorksheet($event->sheet->getDelegate());
    }
}