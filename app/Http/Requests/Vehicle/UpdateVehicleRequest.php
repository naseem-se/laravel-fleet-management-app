<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vehicle'));
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;
        $vehicleId = $this->route('vehicle')->id;

        return [
            'registration_number' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('vehicles', 'registration_number')
                    ->where('company_id', $companyId)
                    ->ignore($vehicleId),
            ],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['nullable', 'string', 'in:petrol,diesel,cng,electric,hybrid'],
            'tank_capacity_litres' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:active,inactive,maintenance'],
            'assigned_driver_id' => [
                'nullable', 'integer',
                Rule::exists('drivers', 'id')->where('company_id', $companyId),
            ],
        ];
    }
}