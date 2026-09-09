<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * membership_status_history — Schema §2.3. FR-6.2, FR-6.6, AC-F6.
 *
 * Append-only record of every transition, whether made by the scheduler or by
 * an administrator.
 *
 * Required to prove AC-F6 and to make expiry overrides accountable: AC-F6 asks
 * that "an admin overriding an expiry date produces an audit log entry naming
 * the admin, the old value and the new value", and this is where the old and
 * new values live.
 *
 * changed_by_user_id NULL means the system scheduler. A row with an actor and
 * no reason is refused in application code, because an override without a
 * stated reason is exactly what the history exists to prevent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_status_history', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();

            $table->timestampTz('from_expires_at')->nullable();
            $table->timestampTz('to_expires_at')->nullable();
            $table->string('reason', 400)->nullable();

            $table->foreignId('changed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestampTz('created_at')->useCurrent();
        });

        DB::statement('ALTER TABLE membership_status_history ADD COLUMN from_status membership_status NULL');
        DB::statement('ALTER TABLE membership_status_history ADD COLUMN to_status membership_status NOT NULL');

        DB::statement(
            'CREATE INDEX membership_status_history_membership_index
             ON membership_status_history (membership_id, created_at DESC)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_status_history');
    }
};
