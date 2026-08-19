<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['company_admin', 'dispatcher', 'driver']);
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:3072', new \App\Rules\ValidImageContent],
        ];
    }
}