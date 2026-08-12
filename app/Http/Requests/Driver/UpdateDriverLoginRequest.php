<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('driver'));
    }

    public function rules(): array
    {
        $driver = $this->route('driver');

        return [
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($driver->user_id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ];
    }
}