<?php

namespace App\Http\Requests\VehicleDocument;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vehicleDocument'));
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'required', 'in:registration,insurance,token,permit,fitness,other'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}