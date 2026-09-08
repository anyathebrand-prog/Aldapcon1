<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Append-only enforcement on activity_log — Schema §2.8, FR-9.8, TRD §11.6.
 *
 * The application database role may INSERT and SELECT. It may NOT UPDATE or
 * DELETE. Pruning (3-year retention, Schema §6.4) is performed by a separate
 * maintenance role in Phase 14.
 *
 * ── On the word "immutable" ──────────────────────────────────────────────
 *
 * FR-9.8 asks for an immutable audit log. This is an APPROXIMATION of
 * immutability and TRD §11.6 is blunt about why: a database table is not
 * immutable to anyone holding superuser or root access — which, in a
 * solo-maintainer setup, is the same person the log exists to record.
 *
 * Genuine immutability needs an external append-only log service, which
 * conflicts with the Nigeria-only residency constraint (TRD §11.2).
 *
 * So: the revoked grants are worth building, and calling the result
 * "immutable" in the privacy policy would not be honest. Log shipping to a
 * separate host with different credentials is the remaining mitigation and
 * belongs to Phase 17.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * The grants apply to the CURRENT database user, which is the application
 * role in every environment (local, CI, staging, production). A superuser is
 * exempt from grant checks by definition, so if the application ever connects
 * as one, this migration silently protects nothing — hence the test.
 */
return new class extends Migration
{
    public function up(): void
    {
        $role = $this->applicationRole();

        if ($role === null) {
            return;
        }

        DB::statement("GRANT INSERT, SELECT ON activity_log TO \"{$role}\"");
        DB::statement("REVOKE UPDATE, DELETE ON activity_log FROM \"{$role}\"");
    }

    public function down(): void
    {
        $role = $this->applicationRole();

        if ($role === null) {
            return;
        }

        DB::statement("GRANT UPDATE, DELETE ON activity_log TO \"{$role}\"");
    }

    /**
     * The role the application connects as. Never assumed from config, because
     * config and the live connection can disagree.
     */
    private function applicationRole(): ?string
    {
        /** @var object{current_user: string}|null $row */
        $row = DB::selectOne('SELECT current_user');

        return $row?->current_user;
    }
};
