<?php

namespace App\Services\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresPhotos
{
    /**
     * Stores under {company_id}/{folder}/... so a leaked/guessed path can
     * never cross tenants, and so wiping a company's storage is one prefix
     * delete rather than a scattered search.
     */
    protected function storePhoto(UploadedFile $file, int $companyId, string $folder): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs(
            "{$companyId}/{$folder}",
            $filename,
            config('filesystems.default')
        );
    }
}