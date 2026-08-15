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

        // Some disk configs return a relative URL (starts with '/'). The
        // frontend lives on a different origin/port than the API, so a
        // relative path resolves against the FRONTEND's own origin and
        // 404s. Force it absolute against the backend's real URL.
        if (str_starts_with($url, '/')) {
            $url = rtrim(config('app.url'), '/').$url;
        }

        return $url;
    }
}