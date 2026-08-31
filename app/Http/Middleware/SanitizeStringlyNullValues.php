<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeStringlyNullValues
{
    public function handle(Request $request, Closure $next): Response
    {
        $sanitized = $this->sanitize($request->all());
        $request->merge($sanitized);

        return $next($request);
    }

    protected function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->sanitize($value);
                continue;
            }

            if (is_string($value) && in_array(strtolower(trim($value)), ['undefined', 'null'], true)) {
                $input[$key] = null;
            }
        }

        return $input;
    }
}