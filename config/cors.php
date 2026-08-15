<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // NEVER '*' in production — every origin here can send authenticated
    // requests once a user has a valid token. Set via .env per environment
    // so local/staging/production each have their own explicit list.
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://localhost:5174'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'], // needed so the frontend's downloadFile() can read the server-provided filename

    'max_age' => 86400,

    'supports_credentials' => true,

];