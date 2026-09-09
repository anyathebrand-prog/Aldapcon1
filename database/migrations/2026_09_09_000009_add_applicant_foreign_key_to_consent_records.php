<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the foreign key deferred from Phase 3 — Schema §2.8, audit IG-1.
 *
 * ── Why it was deferred ──────────────────────────────────────────────────
 *
 * Audit IG-1 moved `consent_records` into Phase 3. Schema §2.8 gives it
 * `applicant_id BIGINT NULL, FK → applicants.id ON DELETE SET NULL`.
 *
 * `applicants` is created in THIS phase. Declaring that key in Phase 3 would
 * have referenced a table that did not exist and the migration would have
 * failed — the same defect audit BC-4 corrected for
 * payments.event_registration_id.
 *
 * The column was created in Phase 3 so no later migration has to rewrite the
 * table; only the constraint waited. It is added here, one migration after
 * `applicants` exists.
 *
 * ── The remaining instance ───────────────────────────────────────────────
 *
 * A sweep of every cross-phase foreign key in the schema found exactly two
 * of these, and this closes one. The other is BC-4: `events` and
 * `event_registrations` must be created in PHASE 8 alongside `payments`, not
 * Phase 12 as the plan in the repository still says. Phase 8 will fail on that
 * foreign key otherwise.
 *
 * ON DELETE SET NULL, matching the schema: consent records are never deleted
 * (§6.2) — they are the evidence the processing was lawful — so severing the
 * link is the correct behaviour when an applicant row is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consent_records', function (Blueprint $table): void {
            $table->foreign('applicant_id')
                ->references('id')->on('applicants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consent_records', function (Blueprint $table): void {
            $table->dropForeign(['applicant_id']);
        });
    }
};
