<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * users — Schema §2.1. FR-4.1 to FR-4.5, FR-9.7.
 *
 * The authentication identity. Every member has one; not every user is a
 * member — admins and publishers have a user row and no membership.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            // Member-locked, admin-editable only (FR-5.2).
            $table->string('full_name', 180);

            // CITEXT, so "Ada@Example.com" and "ada@example.com" are one
            // account. The login identifier (FR-4.1).
            $table->string('email')->unique();

            $table->string('phone', 20);
            $table->string('password');

            $table->timestampTz('email_verified_at')->nullable();

            // Encrypted at rest by Fortify (Phase 4).
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();

            $table->string('remember_token', 100)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_login_at')->nullable();

            // Security only. 90-day retention (Schema §6.4) — a scheduled
            // pruning job in Phase 14, not a column that keeps IPs for ever.
            $table->ipAddress('last_login_ip')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('is_active');
            $table->index('deleted_at');
        });

        // Laravel has no CITEXT column type, so the change is made directly.
        // Done after creation rather than via a raw CREATE TABLE so the rest
        // of the definition stays readable.
        DB::statement('ALTER TABLE users ALTER COLUMN email TYPE CITEXT');

        // A confirmed second factor with no secret is not a state that can
        // mean anything, and 2FA is mandatory for staff (FR-4.3) — so the
        // pairing is enforced by the database rather than trusted to the
        // enrolment flow.
        DB::statement(
            'ALTER TABLE users ADD CONSTRAINT users_two_factor_pairing_check
             CHECK (two_factor_confirmed_at IS NULL OR two_factor_secret IS NOT NULL)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
