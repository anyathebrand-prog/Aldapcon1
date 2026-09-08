<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * login_attempts — Schema §2.1. FR-4.5, AC-F4.
 *
 * The durable record behind the lockout. The live throttle uses the Redis rate
 * limiter for speed; this table is what survives a cache flush and what a
 * security investigation actually reads. Both must agree on the six-attempt
 * threshold (AC-F4).
 *
 * Retention is 90 days (Schema §6.4), pruned by a scheduled job in Phase 14.
 * Without that job this table keeps every IP address that ever touched the
 * login form, indefinitely, at a data protection association.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('email');
            $table->ipAddress('ip_address');
            $table->boolean('successful');
            $table->string('user_agent', 400)->nullable();
            $table->timestampTz('attempted_at')->useCurrent();
        });

        DB::statement('ALTER TABLE login_attempts ALTER COLUMN email TYPE CITEXT');

        // Descending, because every query against this table asks "what
        // happened recently for this email / this IP".
        DB::statement('CREATE INDEX login_attempts_email_recent_index ON login_attempts (email, attempted_at DESC)');
        DB::statement('CREATE INDEX login_attempts_ip_recent_index ON login_attempts (ip_address, attempted_at DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
