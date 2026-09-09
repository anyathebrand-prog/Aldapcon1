<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * email_log — Schema §2.7. FR-10.1, AC-F10.
 *
 * Exists to answer the support question "did they get it?", which otherwise
 * has no answer at all. A member who says their welcome email never arrived is
 * either right or wrong, and without this table nobody can tell which.
 *
 * ── The body is never stored ─────────────────────────────────────────────
 *
 * Metadata only (Schema §2.7). A welcome email contains a membership number; a
 * password reset contains a working token. Keeping the rendered body would
 * turn a support convenience into a second copy of every credential the system
 * has ever sent, retained for a year.
 *
 * Retention is 12 months (Schema §6.4), pruned by a scheduled job in Phase 14.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_log', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('to_email');
            $table->string('mailable', 120);
            $table->string('subject', 250);

            // Loose polymorphic pair, deliberately without a foreign key: the
            // log must survive the thing it refers to being deleted, and it
            // points at several unrelated tables. Unlike `payments`, nothing
            // here depends on referential integrity.
            $table->string('related_type', 40)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->string('provider_message_id', 150)->nullable();
            $table->text('error')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['to_email', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });

        DB::statement('ALTER TABLE email_log ALTER COLUMN to_email TYPE CITEXT');

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'email_status') THEN
                    CREATE TYPE email_status AS ENUM ('queued', 'sent', 'failed');
                END IF;
            END
            $$;
        ");

        DB::statement("ALTER TABLE email_log ADD COLUMN status email_status NOT NULL DEFAULT 'queued'");

        DB::statement('CREATE INDEX email_log_status_index ON email_log (status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('email_log');
        DB::statement('DROP TYPE IF EXISTS email_status');
    }
};
