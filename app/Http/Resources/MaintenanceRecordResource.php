<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'id' => $this->vehicle->id,
                'registration_number' => $this->vehicle->registration_number,
                'current_odometer' => $this->vehicle->current_odometer,
            ]),
            'type' => $this->type,
            'description' => $this->description,
            'cost' => $this->cost,
            'odometer_at_service' => $this->odometer_at_service,
            'service_date' => $this->service_date,
            'next_service_date' => $this->next_service_date,
            'next_service_km' => $this->next_service_km,
            'performed_by' => $this->performed_by,
            'due_status' => $this->dueStatus(),
            'due_summary' => $this->dueSummary(),
            'created_at' => $this->created_at,
        ];
    }

    protected function dueStatus(): string
    {
        if (! $this->next_service_date && ! $this->next_service_km) {
            return 'none';
        }

        $dateOverdue = $this->next_service_date && $this->next_service_date->isPast();
        $kmOverdue = $this->next_service_km && $this->vehicle && $this->vehicle->current_odometer >= $this->next_service_km;

        if ($dateOverdue || $kmOverdue) {
            return 'overdue';
        }

        $dateSoon = $this->next_service_date && $this->next_service_date->isBefore(now()->addDays(7));
        $kmSoon = $this->next_service_km && $this->vehicle
            && ($this->next_service_km - $this->vehicle->current_odometer) <= 500;

        return ($dateSoon || $kmSoon) ? 'due_soon' : 'ok';
    }

    protected function dueSummary(): ?string
    {
        $parts = [];

        if ($this->next_service_date) {
            $parts[] = 'by '.$this->next_service_date->format('M j, Y');
        }

        if ($this->next_service_km) {
            $parts[] = 'or at '.number_format((float) $this->next_service_km).' km';
        }

        return $parts ? implode(' ', $parts) : null;
    }
}