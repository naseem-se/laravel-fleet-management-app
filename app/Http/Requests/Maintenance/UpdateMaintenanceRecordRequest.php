<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('maintenance_record'));
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'in:oil_change,service,repair,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'odometer_at_service' => ['nullable', 'numeric', 'min:0'],
            'service_date' => ['sometimes', 'required', 'date'],
            'next_service_date' => ['nullable', 'date', 'after:service_date'],
            'next_service_km' => ['nullable', 'numeric'],
            'performed_by' => ['nullable', 'string', 'max:150'],
        ];
    }
}