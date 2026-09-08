<?php

declare(strict_types=1);

use App\Console\Commands\Heartbeat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\artisan;

/**
 * Phase 1 check: the scheduler is wired up and `schedule:run` reaches a command.
 *
 * Phase 11 puts the entire membership lifecycle behind this — status
 * transitions at 30 days and at expiry, and the four renewal reminders in
 * FR-6.3. A scheduler that is not running produces no error anywhere; it
 * produces a membership base where nobody ever expires and no reminder is ever
 * sent, which looks like quiet success until a member notices.
 */
it('registers the heartbeat on the schedule', function (): void {
    $schedule = app(Schedule::class);

    $commands = collect($schedule->events())
        ->map(fn ($event): string => (string) $event->command);

    expect($commands->contains(fn (string $c): bool => str_contains($c, 'aldapcon:heartbeat')))
        ->toBeTrue('aldapcon:heartbeat is not on the schedule');
});

it('runs the scheduled command through schedule:run', function (): void {
    Cache::forget(Heartbeat::CACHE_KEY);

    artisan('schedule:run')->assertSuccessful();

    expect(Cache::get(Heartbeat::CACHE_KEY))
        ->not->toBeNull('schedule:run did not reach aldapcon:heartbeat');
});

it('runs the heartbeat command directly', function (): void {
    Cache::forget(Heartbeat::CACHE_KEY);

    artisan('aldapcon:heartbeat')->assertSuccessful();

    expect(Cache::get(Heartbeat::CACHE_KEY))->not->toBeNull();
});
