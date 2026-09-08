<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Records that the scheduler ran.
 *
 * Phase 1 needs a scheduled command to prove `schedule:run` reaches the
 * application at all. This is that command, and it keeps earning its place
 * afterwards: from Phase 11 onward the daily lifecycle transitions and the
 * renewal reminders depend entirely on the scheduler being alive, and a
 * scheduler that has silently stopped looks exactly like a membership base
 * where nobody happens to be expiring.
 *
 * It writes one cache key. It is not a health endpoint and does not touch the
 * database.
 */
final class Heartbeat extends Command
{
    public const CACHE_KEY = 'aldapcon:scheduler:last-run';

    protected $signature = 'aldapcon:heartbeat';

    protected $description = 'Record that the task scheduler ran';

    public function handle(): int
    {
        $now = now()->toIso8601String();

        // Deliberately longer than the schedule interval, so a stopped
        // scheduler leaves a stale value rather than no value at all — an
        // absent key is indistinguishable from a cold cache.
        Cache::put(self::CACHE_KEY, $now, now()->addDay());

        $this->line("Scheduler heartbeat recorded at {$now}");

        return self::SUCCESS;
    }
}
