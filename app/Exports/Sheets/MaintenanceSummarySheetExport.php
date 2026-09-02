<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class MaintenanceSummarySheetExport implements FromArray, WithTitle
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
            ['Maintenance Report Summary'],
            ['Period', $this->data['period']['from'].' to '.$this->data['period']['to']],
            [],
            ['Total Records', $this->data['total_records']],
            ['Total Cost', $this->data['total_cost']],
            ['Currently Overdue', $this->data['overdue_count']],
            ['Due Within 7 Days / 500 km', $this->data['due_soon_count']],
            [],
            ['Cost By Type'],
            ['Type', 'Count', 'Total Cost'],
        ];

        foreach ($this->data['cost_by_type'] as $t) {
            $rows[] = [ucfirst(str_replace('_', ' ', $t['type'])), $t['count'], $t['total_cost']];
        }

        return $rows;
    }
}