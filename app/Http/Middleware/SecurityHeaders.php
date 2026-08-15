<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');

        // Only sent over HTTPS in production — forces browsers to never
        // downgrade to plain HTTP for this domain once seen once.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // This is a JSON API, not a page that renders HTML from user
        // content — a strict CSP here is cheap insurance against any
        // future accidental HTML-rendering endpoint (e.g. a debug page).
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none';");

        return $response;
    }
}