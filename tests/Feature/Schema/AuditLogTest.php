<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * The audit log is append-only — FR-9.8, Schema §2.8, TRD §11.6.
 *
 * Plan Phase 3: "activity_log grants verified: the application role can INSERT
 * and SELECT but UPDATE and DELETE are rejected at the database level."
 *
 * This is the test that matters most in this phase. FR-9.8 requires an
 * immutable audit log, and TRD §11.6 is honest that a table is not immutable
 * to anyone holding superuser access — in a solo-maintainer setup, the same
 * person the log exists to record. What CAN be enforced is that the
 * APPLICATION cannot rewrite history, and that is what is asserted here.
 *
 * If these ever start skipping because the connection is a superuser, the fix
 * is to stop connecting as one (TRD §6.2), not to delete the test.
 */
function connectedAsSuperuser(): bool
{
    /** @var object{usesuper: bool}|null $row */
    $row = DB::selectOne('SELECT usesuper FROM pg_user WHERE usename = current_user');

    if ($row === null) {
        return false;
    }

    return (bool) $row->usesuper;
}

it('can insert into the audit log', function (): void {
    activity()->log('A thing happened');

    expect(Activity::query()->count())->toBe(1);
});

it('can read the audit log', function (): void {
    activity()->log('A thing happened');

    expect(Activity::query()->first()?->description)->toBe('A thing happened');
});

it('rejects updates to the audit log at the database level', function (): void {
    if (connectedAsSuperuser()) {
        // A superuser bypasses grant checks by definition, so the assertion
        // would pass hollowly. Skip loudly rather than claim a guarantee that
        // is not being tested.
        $this->markTestSkipped(
            'Connected as a PostgreSQL superuser, so grants are not enforced. '
            .'Production must not connect as a superuser (TRD §6.2).'
        );
    }

    activity()->log('Original');

    expect(fn () => DB::statement("UPDATE activity_log SET description = 'Rewritten'"))
        ->toThrow(QueryException::class);

    expect(Activity::query()->first()?->description)->toBe('Original');
});

it('rejects deletes from the audit log at the database level', function (): void {
    if (connectedAsSuperuser()) {
        $this->markTestSkipped(
            'Connected as a PostgreSQL superuser, so grants are not enforced. '
            .'Production must not connect as a superuser (TRD §6.2).'
        );
    }

    activity()->log('Original');

    expect(fn () => DB::statement('DELETE FROM activity_log'))
        ->toThrow(QueryException::class);

    expect(Activity::query()->count())->toBe(1);
});

it('records who caused an entry', function (): void {
    // FR-9.8 — actor, action, target, timestamp. Without the actor the log
    // answers "what happened" but never "who did it", which is the question
    // an audit actually asks.
    $user = User::factory()->create();

    activity()->causedBy($user)->log('Deactivated a membership');

    $entry = Activity::query()->first();

    expect($entry?->causer_id)->toBe($user->id)
        ->and($entry?->created_at)->not->toBeNull();
});
