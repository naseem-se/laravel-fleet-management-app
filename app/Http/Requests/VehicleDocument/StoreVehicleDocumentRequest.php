<?php

namespace App\Http\Requests\VehicleDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\VehicleDocument::class);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required', 'integer',
                Rule::exists('vehicles', 'id')->where('company_id', $this->user()->company_id),
            ],
            'document_type' => ['required', 'in:registration,insurance,token,permit,fitness,other'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}