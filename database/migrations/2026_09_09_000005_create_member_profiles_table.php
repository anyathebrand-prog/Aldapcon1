<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * member_profiles — Schema §2.3. FR-3.6, FR-5.2.
 *
 * One-to-one with users, holding the full registration fields.
 *
 * Separate from `users` because these are member attributes, not
 * authentication attributes: an admin or publisher account has none of them.
 * Putting them on `users` would mean every staff account carrying empty
 * columns about an organisation they do not belong to.
 *
 * Data minimisation (TRD §6.3): only the fields enumerated in FR-3.6 are
 * collected. Any new column here needs a stated purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table): void {
            // CASCADE, unusually — this row is part of the person, not a
            // financial record. Erasure hard-deletes it (Schema §6.3) while
            // payments survive anonymised.
            $table->foreignId('user_id')->primary()
                ->constrained('users')->cascadeOnDelete();

            $table->string('organisation', 200)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->text('qualifications')->nullable();

            // Required in application validation when the category has
            // requires_verification = true — a rule that spans two tables, so
            // it cannot be a column constraint (Schema §2.3).
            $table->string('ndpc_licence_number', 60)->nullable();

            $table->string('state', 60);
            $table->string('city', 80);
            $table->string('referral_source', 120)->nullable();

            $table->timestampsTz();

            // FR-9.2 — admin search by organisation.
            $table->index('organisation');
            $table->index('ndpc_licence_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
