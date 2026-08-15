<?php

namespace App\Services\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresPhotos
{
    protected function storePhoto(UploadedFile $file, int $companyId, string $folder): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs(
            "{$companyId}/{$folder}",
            $filename,
            config('filesystems.default')
        );
    }

    protected function deletePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            $disk = Storage::disk(config('filesystems.default'));

            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete stored file', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }

    protected function replacePhoto(UploadedFile $newFile, ?string $oldPath, int $companyId, string $folder): string
    {
        $newPath = $this->storePhoto($newFile, $companyId, $folder);

        $this->deletePhoto($oldPath);

        return $newPath;
    }
}