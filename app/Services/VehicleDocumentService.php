<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\VehicleDocument;
use App\Services\Concerns\StoresPhotos;
use Illuminate\Support\Facades\DB;

class VehicleDocumentService
{
    use StoresPhotos;

    public function create(int $companyId, array $data): VehicleDocument
    {
        return DB::transaction(function () use ($companyId, $data) {
            $filePath = isset($data['file'])
                ? $this->storePhoto($data['file'], $companyId, 'vehicle-documents')
                : null;

            $document = VehicleDocument::create([
                'vehicle_id' => $data['vehicle_id'],
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'file_path' => $filePath,
            ]);

            if ($document->expiry_date) {
                Reminder::create([
                    'reminder_type' => 'document',
                    'reference_type' => VehicleDocument::class,
                    'reference_id' => $document->id,
                    'due_date' => $document->expiry_date,
                    'status' => 'pending',
                ]);
            }

            return $document;
        });
    }

    public function delete(VehicleDocument $document): void
    {
        DB::transaction(function () use ($document) {
            Reminder::where('reminder_type', 'document')
                ->where('reference_type', VehicleDocument::class)
                ->where('reference_id', $document->id)
                ->delete();

            $document->delete();
        });
    }
}