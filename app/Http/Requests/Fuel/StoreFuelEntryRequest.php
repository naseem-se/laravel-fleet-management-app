<?php

namespace App\Http\Requests\Fuel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFuelEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('driver') && $this->user()->driver !== null;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required', 'integer',
                Rule::exists('vehicles', 'id')->where('company_id', $this->user()->company_id),
            ],
            'journey_id' => [
                'nullable', 'integer',
                Rule::exists('journeys', 'id')->where('company_id', $this->user()->company_id),
            ],
            // Driver enters what they actually paid and the price per
            // litre off the pump — litres are derived from these two,
            // never typed in directly (a pump receipt shows total and
            // rate, not a pre-calculated litre figure most drivers would
            // get wrong doing the division by hand).
            'total_price' => ['required', 'numeric', 'min:0.01'],
            'rate_per_litre' => ['required', 'numeric', 'min:0.01'],
            'odometer_reading' => ['required', 'numeric', 'min:0'],
            'receipt_photo' => ['required', 'image', 'max:5120', new \App\Rules\ValidImageContent],
        ];
    }
}