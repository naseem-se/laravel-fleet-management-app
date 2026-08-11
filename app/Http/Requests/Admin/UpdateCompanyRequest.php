<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'slug' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', Rule::unique('companies', 'slug')->ignore($companyId)],
            'timezone' => ['nullable', 'timezone'],
        ];
    }
}