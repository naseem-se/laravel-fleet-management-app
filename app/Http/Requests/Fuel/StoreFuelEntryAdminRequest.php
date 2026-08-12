<?php

namespace App\Http\Requests\Fuel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFuelEntryAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('company_id', $companyId)],
            'driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')->where('company_id', $companyId)],
            'journey_id' => ['nullable', 'integer', Rule::exists('journeys', 'id')->where('company_id', $companyId)],
            'quantity_litres' => ['required', 'numeric', 'min:0.1'],
            'rate_per_litre' => ['required', 'numeric', 'min:0.01'],
            'odometer_reading' => ['required', 'numeric', 'min:0'],
            'entry_time' => ['nullable', 'date'],
            // no photo required here — this is for backfilling/cash entries without the PWA
        ];
    }
}