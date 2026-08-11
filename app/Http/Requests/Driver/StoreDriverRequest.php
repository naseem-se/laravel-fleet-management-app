<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Driver::class);
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'cnic_number' => ['nullable', 'string', 'max:30'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'license_expiry_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],

            // Optional: create a login account for this driver at the same time.
            // If omitted, the driver has no portal login yet and can be linked later.
            'create_login' => ['sometimes', 'boolean'],
            'email' => [
                'required_if:create_login,true', 'nullable', 'email',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required_if:create_login,true', 'nullable', 'string', 'min:8'],
        ];
    }
}