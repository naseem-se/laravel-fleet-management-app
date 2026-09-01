<?php

namespace App\Http\Requests\Journey;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateDetails', $this->route('journey'));
    }

    public function rules(): array
    {
        return [
            'purpose' => ['nullable', 'string', 'max:150'],
            'detail_of_journey' => ['nullable', 'string', 'max:1000'],
            'officer_name' => ['nullable', 'string', 'max:150'],
            'signature' => ['nullable', 'string', 'max:150'],
            'start_km' => ['sometimes', 'required', 'numeric', 'min:0'],
            'end_km' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }
}