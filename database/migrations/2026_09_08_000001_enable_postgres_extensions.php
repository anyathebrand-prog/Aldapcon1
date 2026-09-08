<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL extensions — Schema §8.1.
 *
 * citext   — case-insensitive text. users.email, applicants.email,
 *            login_attempts.email and consent_records.email are all CITEXT, so
 *            "Ada@Example.com" and "ada@example.com" are one person. Doing this
 *            with lower() in application code means every lookup that forgets
 *            the call becomes a duplicate-account bug.
 *
 * pgcrypto — gen_random_uuid(), the default for every uuid column. Generating
 *            UUIDs in the database means a row inserted by a console command,
 *            a seeder or a manual psql session gets one too.
 *
 * Both must exist before any table that uses them.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
    }

    public function down(): void
    {
        // Deliberately not dropped. Dropping an extension that other objects
        // depend on fails, and dropping one nothing depends on achieves
        // nothing. Rolling back a migration should not reach outside the
        // schema it created.
    }
};
