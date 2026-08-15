<?php

namespace App\Http\Requests\Fuel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFuelEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function rules(): array
    {
        return [
            'quantity_litres' => ['sometimes', 'required', 'numeric', 'min:0.1'],
            'rate_per_litre' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'odometer_reading' => ['sometimes', 'required', 'numeric', 'min:0'],
            'entry_time' => ['sometimes', 'required', 'date'],
            'receipt_photo' => ['nullable', 'image', 'max:5120', new \App\Rules\ValidImageContent],
        ];
    }
}