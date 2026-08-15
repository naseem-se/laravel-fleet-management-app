<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Laravel's `image`/`mimes` rules check the Content-Type header and file
 * extension, both of which a malicious client can spoof (e.g. upload a PHP
 * script renamed to photo.jpg with a forged Content-Type). This checks the
 * actual file signature/magic bytes instead, using PHP's finfo extension
 * which inspects real file content.
 */
class ValidImageContent implements ValidationRule
{
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be a valid uploaded file.');
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMimeType = finfo_file($finfo, $value->getRealPath());
        finfo_close($finfo);

        if (! in_array($realMimeType, $this->allowedMimes, true)) {
            $fail('The :attribute must be a genuine JPEG, PNG, or WebP image.');
        }
    }
}