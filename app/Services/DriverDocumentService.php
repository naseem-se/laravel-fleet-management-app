<?php

namespace App\Services;

use App\Models\DriverDocument;
use App\Services\Concerns\StoresPhotos;
use Illuminate\Support\Facades\DB;

class DriverDocumentService
{
    use StoresPhotos;

    public function create(int $companyId, int $driverId, array $data): DriverDocument
    {
        return DB::transaction(function () use ($companyId, $driverId, $data) {
            $filePath = isset($data['file'])
                ? $this->storePhoto($data['file'], $companyId, 'driver-documents')
                : null;

            return DriverDocument::create([
                'driver_id' => $driverId,
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'file_path' => $filePath,
            ]);
        });
    }

    public function update(DriverDocument $document, array $data): DriverDocument
    {
        return DB::transaction(function () use ($document, $data) {
            if (isset($data['file'])) {
                $data['file_path'] = $this->replacePhoto(
                    $data['file'],
                    $document->file_path,
                    $document->company_id,
                    'driver-documents'
                );
            }
            unset($data['file']);

            $document->update($data);

            return $document->fresh();
        });
    }

    public function delete(DriverDocument $document): void
    {
        DB::transaction(function () use ($document) {
            $this->deletePhoto($document->file_path);

            $document->delete();
        });
    }
}