<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * consent_records — Schema §2.8. FR-12.2, AC-F12.
 *
 * Append-only evidence of lawful basis. Withdrawal is a NEW ROW with
 * granted = false; the current state is the latest row per (email, purpose).
 * Never an update, because an updated consent record destroys the evidence of
 * what was consented to and when.
 *
 * Retained indefinitely (Schema §6.4) and NOT deleted by erasure (§6.3) —
 * these records are what prove the processing was lawful in the first place.
 *
 * ─────────────────────────────────────────────────────────────────────────
 * ORDERING NOTE — the same class of defect as audit BC-4.
 *
 * Audit IG-1 moved this table into Phase 3. Schema §2.8 gives it
 * `applicant_id BIGINT NULL, FK → applicants.id ON DELETE SET NULL`.
 *
 * `applicants` is not created until PHASE 6. Declaring that foreign key here
 * would reference a table that does not exist and the migration would fail —
 * exactly what BC-4 corrected for payments.event_registration_id.
 *
 * Resolution: the COLUMN is created now, so no later migration has to rewrite
 * the table, and the CONSTRAINT is added in Phase 6 alongside `applicants`.
 *
 * Do not "fix" this by adding the constraint here. It will not run.
 * ─────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // FK deferred to Phase 6 — see the ordering note above.
            $table->unsignedBigInteger('applicant_id')->nullable();

            // The anchor. A consent may be given before any user or applicant
            // row exists (the newsletter block on the news page, FR-7.5), so
            // the email is what every consent is keyed to.
            $table->string('email');

            $table->foreignId('policy_version_id')->nullable()
                ->constrained('policy_versions')->restrictOnDelete();

            $table->boolean('granted');

            // The exact wording shown at the time. The policy version says
            // which document; this says which sentence they agreed to.
            $table->text('consent_text_snapshot');

            $table->ipAddress('ip_address');
            $table->string('user_agent', 400)->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('applicant_id');
        });

        DB::statement('ALTER TABLE consent_records ALTER COLUMN email TYPE CITEXT');

        // purpose is an enum type, which Laravel's Blueprint cannot express.
        DB::statement('ALTER TABLE consent_records ADD COLUMN purpose consent_purpose NOT NULL');

        // "What is the current state of this consent?" is always a lookup of
        // the latest row for an email and purpose.
        DB::statement(
            'CREATE INDEX consent_records_email_purpose_recent_index
             ON consent_records (email, purpose, created_at DESC)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
