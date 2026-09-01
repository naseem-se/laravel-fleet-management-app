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
            'photo' => ['required', 'image', 'max:5120', new \App\Rules\ValidImageContent],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'signature' => ['nullable', 'string', 'max:150'],
            // pol_drawn / pol_invoice_photo intentionally removed — any
            // fuel purchased during a trip is logged as its own Fuel Entry
            // (linked via journey_id) while the journey is active. The
            // report pulls fuel totals and receipts from those linked
            // entries instead of asking the driver to re-enter the same
            // information a second time at journey end.
        ];
    }
}