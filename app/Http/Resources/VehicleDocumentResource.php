<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'is_expiring_soon' => $this->isExpiringSoon(),
            'file_url' => FileUrl::for($this->file_path),
        ];
    }
}