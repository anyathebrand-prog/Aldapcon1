<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 check: PostgreSQL 16 is reachable and is the driver in use.
 *
 * These read as trivial, and that is the point. Every later phase assumes
 * Postgres-specific behaviour that no other engine provides — CITEXT columns
 * (Schema §2.1), partial unique indexes (Schema §2.3), JSONB, generated
 * TSVECTOR columns for FR-1.6, and num_nonnulls in the payments CHECK
 * constraint (Schema §2.4). Discovering in Phase 6 that CI silently ran on
 * SQLite would invalidate every migration written up to that point.
 */
it('connects to postgresql', function (): void {
    expect(DB::connection()->getDriverName())->toBe('pgsql');
});

it('runs postgresql 16 or above', function (): void {
    /** @var object{server_version: string} $row */
    $row = DB::selectOne('SHOW server_version');

    expect(version_compare($row->server_version, '16', '>='))
        ->toBeTrue("PostgreSQL {$row->server_version} found; TRD §2.2 requires 16+");
});

it('has run its migrations', function (): void {
    expect(Schema::hasTable('migrations'))->toBeTrue();
});

it('supports the postgres features the schema depends on', function (): void {
    // num_nonnulls backs the payments "exactly one target" constraint
    // (Schema §2.4). Asserted here so a database swap fails in Phase 1
    // rather than at the final commit of a real signup.
    /** @var object{count: int} $row */
    $row = DB::selectOne('SELECT num_nonnulls(1, NULL, NULL) AS count');

    expect((int) $row->count)->toBe(1);
});
