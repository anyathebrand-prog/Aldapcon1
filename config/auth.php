<?php

declare(strict_types=1);

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
     * Session-based authentication (TRD §2.3). Sessions live in Redis, which
     * is why there is no sessions table and why App Flow M-11 (active session
     * management) was deferred by audit BC-6.
     */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            // Not App\Models\User. Identity is a domain (TRD §1.2), and the
            // model lives with the rest of it.
            'model' => App\Domain\Identity\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            // FR-4.2 — reset links expire and are single-use. The row is
            // deleted on use; this is the ceiling if it is never used.
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
     * How long a password confirmation lasts before it is asked for again.
     * Relevant from Phase 4, where 2FA enrolment and password change both sit
     * behind it.
     */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
