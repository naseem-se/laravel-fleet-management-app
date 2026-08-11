<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class FileUrl
{
    /**
     * Wraps Storage::disk()->url() behind a concrete FilesystemAdapter type
     * hint (rather than the Filesystem contract, which doesn't declare url())
     * so this is the one place that needs the disk-driver knowledge —
     * Resources just call FileUrl::for($path).
     */
    public static function for(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default'));

        return $disk->url($path);
    }
}