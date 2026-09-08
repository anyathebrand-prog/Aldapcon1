<?php

declare(strict_types=1);

return [
    // TRD §2.3 — Redis. Every email in FR-10.1, the Paystack webhook
    // processing in Phase 8 and the activation path in Phase 9a run through
    // this connection.
    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'job_batches',
    ],

    /*
     * Schema §2.9: a failed activation job is a paid member with no
     * membership, so failures must be durable and visible rather than
     * discarded. They are kept in the database, not in Redis, so they survive
     * a cache flush. Alerting on this table is Phase 8 work.
     */
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],
];
