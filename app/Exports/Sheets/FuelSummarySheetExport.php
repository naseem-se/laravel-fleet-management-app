<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FuelSummarySheetExport implements FromArray, WithTitle
{
    public function __construct(protected array $data)
    {
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $rows = [
            ['Fuel Report Summary'],
            ['Period', $this->data['period']['from'].' to '.$this->data['period']['to']],
            [],
            ['Total Litres Purchased', $this->data['total_litres']],
            ['Total Cost', $this->data['total_cost']],
            ['Average Rate/Litre', $this->data['avg_rate'] ?? '-'],
            [],
            ['By Vehicle'],
            ['Vehicle', 'Entries', 'Total Litres', 'Total Cost', 'Avg Rate/Litre'],
        ];

        foreach ($this->data['per_vehicle'] as $v) {
            $rows[] = [$v['vehicle'], $v['entries'], $v['total_litres'], $v['total_cost'], $v['avg_rate'] ?? '-'];
        }

        return $rows;
    }
}