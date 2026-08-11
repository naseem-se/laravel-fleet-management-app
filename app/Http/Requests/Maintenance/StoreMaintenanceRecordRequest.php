<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MaintenanceRecord::class);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required', 'integer',
                Rule::exists('vehicles', 'id')->where('company_id', $this->user()->company_id),
            ],
            'type' => ['required', 'in:oil_change,service,repair,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'odometer_at_service' => ['nullable', 'numeric', 'min:0'],
            'service_date' => ['required', 'date'],
            'next_service_date' => ['nullable', 'date', 'after:service_date'],
            'next_service_km' => ['nullable', 'numeric', 'gt:odometer_at_service'],
            'performed_by' => ['nullable', 'string', 'max:150'],
        ];
    }
}