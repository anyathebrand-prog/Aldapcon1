<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    // TRD §2.2 — PostgreSQL 16. Not configurable by accident.
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'aldapcon'),
            'username' => env('DB_USERNAME', 'aldapcon'),
            'password' => env('DB_PASSWORD', ''),
            // AC-F9: Nigerian names must survive CSV export to Excel.
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        /*
         * A second, independent connection to the same database.
         *
         * Exists for one purpose: proving that the membership number counter
         * is genuinely locked during allocation (FR-3.11, AC-F3). A test using
         * the default connection cannot observe its own lock — it already
         * holds it — so the assertion would pass whether or not the lock was
         * ever taken, which is the failure mode that produces duplicate
         * membership numbers in production.
         *
         * Not used by application code. If it ever is, that is a bug: two
         * connections mean two transactions, and the activation transaction
         * in Schema §4.3 has to commit or roll back as one.
         */
        'pgsql_second' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'aldapcon'),
            'username' => env('DB_USERNAME', 'aldapcon'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'aldapcon'), '_').'_database_'),
            'persistent' => (bool) env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
