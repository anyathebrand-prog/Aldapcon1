<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * renewal_reminders — Schema §2.3. FR-6.3, AC-F6.
 *
 * Guarantees each reminder fires exactly once per cycle.
 *
 * ── Why cycle_expires_at is in the unique key ────────────────────────────
 *
 * Schema §2.3: keying on (membership_id, stage) alone "would send the 30-day
 * reminder once in the member's lifetime and never again after their first
 * renewal — a bug that would not surface until year two."
 *
 * By then there would be a year of members who quietly stopped being reminded,
 * and the first evidence would be a drop in renewals nobody could explain.
 * The extra column costs nothing and removes that entirely.
 *
 * The uniqueness is enforced by the DATABASE rather than by a "have I sent
 * this?" query, because under concurrent scheduler runs the query races and
 * the constraint does not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_reminders', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
            $table->timestampTz('cycle_expires_at');
            $table->timestampTz('sent_at')->useCurrent();
        });

        DB::statement('ALTER TABLE renewal_reminders ADD COLUMN stage reminder_stage NOT NULL');

        DB::statement(
            'CREATE UNIQUE INDEX renewal_reminders_once_per_cycle
             ON renewal_reminders (membership_id, cycle_expires_at, stage)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_reminders');
    }
};
