<?php

namespace App\Http\Requests\Journey;

use Illuminate\Foundation\Http\FormRequest;

class EndJourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('journey'));
    }

    public function rules(): array
    {
        return [
            'end_km' => ['required', 'numeric', 'min:0'],
            'photo' => ['required', 'image', 'max:5120'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'signature' => ['nullable', 'string', 'max:150'],
            'pol_drawn' => ['nullable', 'numeric', 'min:0'],
            // Only required to be a genuine image if actually provided —
            // no invoice at all is a perfectly normal, common case.
            'pol_invoice_photo' => ['nullable', 'image', 'max:5120', new \App\Rules\ValidImageContent],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}