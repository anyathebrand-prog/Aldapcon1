<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * applicants — Schema §2.3. FR-3.2, FR-3.8, FR-3.9, FR-3.13, change set 01.
 *
 * The paid-but-not-yet-member entity, and one of the three structural
 * decisions in the schema.
 *
 * The signup flow takes payment BEFORE registration (FR-3.1), so there is a
 * real, persistent thing that has paid and is not yet a member. Modelling that
 * as a half-written member row would put invalid members in `memberships`.
 * This table exists so "awaiting registration" is a first-class state with its
 * own lifecycle rather than an absence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            $table->foreignId('category_id')
                ->constrained('membership_categories')->restrictOnDelete();

            $table->string('full_name', 180);
            $table->string('email');
            $table->string('phone', 20);

            // The amount QUOTED. payments.amount_kobo holds what was actually
            // charged, so a fee change between quote and payment is visible
            // rather than silently reconciled.
            $table->bigInteger('fee_kobo_at_initiation');

            /*
             * The registration link (FR-3.8).
             *
             * Stored HASHED, never in plaintext — the plaintext lives only in
             * the email. Schema §2.3: "A leaked database backup must not hand
             * somebody the ability to complete another person's registration
             * and set its password."
             */
            $table->string('registration_token_hash', 64)->nullable()->unique();
            $table->timestampTz('token_expires_at')->nullable();
            $table->smallInteger('token_issued_count')->default(0);

            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('registered_at')->nullable();

            $table->foreignId('membership_id')->nullable()
                ->constrained('memberships')->nullOnDelete();

            // The account exists before the membership does on the
            // verification path (change set 01, transaction T3a).
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Change set 01 — the administrator decision.
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('review_reason', 500)->nullable();
            $table->smallInteger('correction_requested_count')->default(0);

            $table->timestampsTz();

            $table->index('email');
            $table->index('category_id');
        });

        DB::statement("ALTER TABLE applicants ADD COLUMN status applicant_status NOT NULL DEFAULT 'initiated'");
        DB::statement('ALTER TABLE applicants ADD COLUMN review_decision review_decision NULL');
        DB::statement('ALTER TABLE applicants ALTER COLUMN email TYPE CITEXT');

        // D-04 "awaiting registration" queue, oldest first.
        DB::statement('CREATE INDEX applicants_status_paid_index ON applicants (status, paid_at)');

        // D-22 verification queue. Oldest first is not a preference: a
        // newest-first queue lets the oldest application rot, and that
        // applicant has already paid (App Flow D-22).
        DB::statement('CREATE INDEX applicants_status_submitted_index ON applicants (status, submitted_at)');

        /*
         * One in-flight application per email address (App Flow J-02).
         *
         * Partial, covering only the live states. A rejected or abandoned
         * applicant must not block somebody from applying again — a refusal
         * is not a permanent ban.
         */
        DB::statement(
            "CREATE UNIQUE INDEX applicants_one_in_flight_per_email
             ON applicants (email)
             WHERE status IN ('initiated', 'paid', 'pending_verification')"
        );

        // Each state must carry the evidence that it happened.
        DB::statement(
            "ALTER TABLE applicants ADD CONSTRAINT applicants_paid_check
             CHECK (status <> 'paid' OR paid_at IS NOT NULL)"
        );

        DB::statement(
            "ALTER TABLE applicants ADD CONSTRAINT applicants_registered_check
             CHECK (status <> 'registered' OR (registered_at IS NOT NULL AND membership_id IS NOT NULL))"
        );

        DB::statement(
            "ALTER TABLE applicants ADD CONSTRAINT applicants_submitted_check
             CHECK (status <> 'pending_verification' OR submitted_at IS NOT NULL)"
        );

        // FR-3.13.4 — rejection requires a reason. Enforced here so a refusal
        // can never be recorded without one, whatever code path issues it.
        DB::statement(
            "ALTER TABLE applicants ADD CONSTRAINT applicants_rejected_check
             CHECK (status <> 'rejected' OR (reviewed_at IS NOT NULL AND review_reason IS NOT NULL))"
        );

        // Any state past registration implies an account exists.
        DB::statement(
            "ALTER TABLE applicants ADD CONSTRAINT applicants_user_check
             CHECK (status NOT IN ('pending_verification', 'registered', 'rejected') OR user_id IS NOT NULL)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
