<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    /*
     * TRD §2.3 — sessions live in Redis.
     *
     * Schema §2.1 records that there is therefore no sessions table, which is
     * why App Flow M-11 (view and revoke active sessions) was deferred by
     * audit BC-6. Changing this driver silently invalidates that reasoning;
     * tests/Feature/Infrastructure/RedisTest.php asserts it stays put.
     */
    'driver' => env('SESSION_DRIVER', 'redis'),

    // FR-4.4 — sessions expire after a period of inactivity and on logout.
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,

    'encrypt' => (bool) env('SESSION_ENCRYPT', false),
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'aldapcon'), '_').'_session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),

    // TRD §2.3 — Secure, HttpOnly, SameSite=Lax. Secure is driven by the
    // environment so local HTTP still works; production sets it true.
    'secure' => (bool) env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    'partitioned' => false,
];
