<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class FileUrl
{
    public static function for(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default'));
        $url = $disk->url($path);

        if (str_starts_with($url, '/')) {
            $url = rtrim(config('app.url'), '/').$url;
        }

        return $url;
    }
}