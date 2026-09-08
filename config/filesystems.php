<?php

declare(strict_types=1);

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        /*
         * The private disk (Schema §6.1, TRD §11.7).
         *
         * NDPC certificates uploaded for verification, generated receipts and
         * data-subject exports live here. It is outside the web root, is not
         * symlinked into public/, and is additionally denied at nginx.
         *
         * Nothing on this disk is ever served by URL. Files are streamed by a
         * controller after a policy check (FR-3.13.2, screen D-24). A
         * certificate reachable by guessing a path is a data breach with
         * extra steps.
         */
        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'visibility' => 'private',
            'throw' => true,
            'report' => false,
        ],

        // Publicly served content images only — post and event artwork,
        // leadership photographs. Never member documents.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
