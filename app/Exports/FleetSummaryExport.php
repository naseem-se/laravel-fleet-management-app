<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FleetSummaryExport implements WithMultipleSheets
{
    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new FleetSummaryOverviewSheet($this->data),
            new FleetSummaryPerVehicleSheet($this->data['per_vehicle']),
        ];
    }
}

class FleetSummaryOverviewSheet implements FromArray, WithTitle
{
    public function __construct(protected array $data)
    {
    }

    public function title(): string
    {
        return 'Overview';
    }

    public function array(): array
    {
        return [
            ['Fleet Summary Report'],
            ['Period', $this->data['period_label'] ?? $this->data['month']],
            [],
            ['Fleet Overview'],
            ['Total Vehicles', $this->data['vehicles']['total']],
            ['Active', $this->data['vehicles']['active']],
            ['In Maintenance', $this->data['vehicles']['maintenance']],
            ['Inactive', $this->data['vehicles']['inactive']],
            [],
            ['Activity This Month'],
            ['Total Journeys', $this->data['total_journeys']],
            ['Total Distance (km)', $this->data['total_distance']],
            ['Fleet Avg Efficiency (km/L)', $this->data['fleet_avg_kmpl'] ?? '-'],
            ['Total Fuel Purchased (L)', $this->data['total_fuel_litres']],
            ['Total Fuel Cost', $this->data['total_fuel_cost']],
            ['Total Maintenance Cost', $this->data['total_maintenance_cost']],
            [],
            ['Maintenance Status (Fleet-Wide, As Of Today)'],
            ['Currently Overdue', $this->data['vehicles_overdue_maintenance']],
            ['Due Within 7 Days / 500 km', $this->data['vehicles_due_soon_maintenance']],
        ];
    }
}

class FleetSummaryPerVehicleSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping, WithTitle
{
    public function __construct(protected \Illuminate\Support\Collection $perVehicle)
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->perVehicle;
    }

    public function title(): string
    {
        return 'Per-Vehicle Breakdown';
    }

    public function headings(): array
    {
        return ['Vehicle', 'Trips', 'Distance (km)', 'Fuel Purchased (L)', 'Fuel Cost', 'Efficiency (km/L)', 'Maintenance Cost'];
    }

    public function map($row): array
    {
        return [
            $row['vehicle'],
            $row['trips'],
            $row['distance'],
            $row['fuel_litres'],
            $row['fuel_cost'],
            $row['kmpl'] ?? '-',
            $row['maintenance_cost'],
        ];
    }
}