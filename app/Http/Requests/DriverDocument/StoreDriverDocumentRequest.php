<?php

namespace App\Http\Requests\DriverDocument;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['company_admin', 'dispatcher']);
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'in:license,cnic,medical_certificate,other'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}