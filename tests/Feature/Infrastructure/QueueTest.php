<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

use Tests\Support\RecordsQueueHeartbeat;

/**
 * Phase 1 check: a dispatched job is actually consumed by a worker.
 *
 * This test deliberately does NOT use Queue::fake(). A fake proves the
 * application called dispatch(); it proves nothing about whether a worker
 * exists, whether it can reach Redis, or whether it can deserialise the job.
 * Here the job goes onto the real queue and a real `queue:work --once`
 * drains it.
 *
 * The plan flags exactly this: "The one real risk is skipping the queue and
 * scheduler setup here and discovering in Phase 7 that emails never send."
 * Every email in FR-10.1, the webhook processing in Phase 8, and the activation
 * path in Phase 9a run through this machinery.
 */
it('dispatches a job onto the redis queue and a worker consumes it', function (): void {
    Cache::forget(RecordsQueueHeartbeat::CACHE_KEY);

    $before = Queue::size();
    $token = uniqid('queue-', true);

    RecordsQueueHeartbeat::dispatch($token);

    // The job is queued, and nothing has run it yet. Asserting both halves is
    // what distinguishes "the worker ran it" from "it never queued at all" —
    // a job that silently fails to enqueue would otherwise look identical to
    // one a worker declined to pick up.
    expect(Queue::size())->toBe($before + 1)
        ->and(Cache::get(RecordsQueueHeartbeat::CACHE_KEY))->toBeNull();

    artisan('queue:work', [
        '--once' => true,
        '--stop-when-empty' => true,
    ])->assertSuccessful();

    expect(Cache::get(RecordsQueueHeartbeat::CACHE_KEY))->toBe($token)
        ->and(Queue::size())->toBe($before);
});
