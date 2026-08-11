<?php

namespace App\Http\Requests\Journey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartJourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Journey::class);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required', 'integer',
                Rule::exists('vehicles', 'id')->where('company_id', $this->user()->company_id),
            ],
            'start_km' => ['required', 'numeric', 'min:0'],
            'photo' => ['required', 'image', 'max:5120'], // 5MB — client should compress before upload, see PWA notes
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}