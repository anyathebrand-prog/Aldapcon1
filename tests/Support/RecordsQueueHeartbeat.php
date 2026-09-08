<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Test-only job used to prove a real worker consumes a real queue.
 *
 * It lives in tests/ rather than app/Jobs/ on purpose: nothing in the shipped
 * application should exist only to be tested. Phase 8 replaces this proof with
 * ProcessPaystackWebhook, where the same guarantee stops being academic.
 */
final class RecordsQueueHeartbeat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const CACHE_KEY = 'aldapcon:test:queue-heartbeat';

    public function __construct(private readonly string $token) {}

    public function handle(): void
    {
        Cache::put(self::CACHE_KEY, $this->token, now()->addMinutes(5));
    }
}
