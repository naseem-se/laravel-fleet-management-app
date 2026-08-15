<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('company_admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'timezone' => ['nullable', 'timezone'],
            'gps_ping_interval_seconds' => ['nullable', 'integer', 'min:60', 'max:1800'],
            'distance_unit' => ['nullable', 'in:km,mi'],
        ];
    }
}