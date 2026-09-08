<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Phase 1 check: Redis is reachable and is the cache, session and queue store.
 *
 * The session driver assertion is load-bearing beyond this phase. Schema §2.1
 * records that there is no sessions table *because* sessions live in Redis,
 * which is why App Flow M-11 (active session management) was deferred by
 * audit BC-6. If the driver quietly changes, that reasoning stops holding and
 * nobody finds out from the code.
 */
it('reaches redis', function (): void {
    expect(Redis::connection()->ping())->not->toBeFalse();
});

it('uses redis for the cache', function (): void {
    expect(config('cache.default'))->toBe('redis');
});

it('uses redis for sessions', function (): void {
    expect(config('session.driver'))->toBe('redis');
});

it('uses redis for the queue', function (): void {
    expect(config('queue.default'))->toBe('redis');
});

it('round-trips a value through the cache', function (): void {
    $key = 'aldapcon:test:'.uniqid();

    Cache::put($key, 'kobo', 60);

    expect(Cache::get($key))->toBe('kobo');

    Cache::forget($key);

    expect(Cache::get($key))->toBeNull();
});
