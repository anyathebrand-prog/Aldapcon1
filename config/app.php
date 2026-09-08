<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'ALDAPCON'),
    'env' => env('APP_ENV', 'production'),

    // TRD §9 makes APP_DEBUG=false in production a launch gate. The default
    // here is the safe one, so a missing key never turns debug on.
    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    // Schema §1.4 — stored UTC, rendered Africa/Lagos at the presentation
    // layer. Storing Lagos time would make every expiry comparison in
    // Phase 11 ambiguous across the DST-free but offset-bearing boundary.
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_NG'),

    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
