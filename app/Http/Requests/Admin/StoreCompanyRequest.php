<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by role:super_admin middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('companies', 'slug')],
            'timezone' => ['nullable', 'timezone'],

            // Initial admin login for the company, created together with it
            'admin_name' => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8'],

            // Optional: put the company on a plan immediately
            'subscription_plan_id' => ['nullable', 'integer', Rule::exists('subscription_plans', 'id')],
            'trial_days' => ['nullable', 'integer', 'min:0'],
        ];
    }
}