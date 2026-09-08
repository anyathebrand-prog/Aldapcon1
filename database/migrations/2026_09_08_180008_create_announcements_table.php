<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * announcements — Schema §2.6. FR-5.5, screens M-06 and D-15.
 *
 * THE ONLY TABLE IN THE SCHEMA CREATED FROM INFERENCE RATHER THAN A STATED
 * REQUIREMENT (App Flow G-6, plan C-2).
 *
 * FR-5.5 gives members announcements. Nothing in FR-9 or FR-10 says who
 * creates them or how. Without this table and the D-15 screen, FR-5.5 is
 * unimplementable — so the plan says build it, and asks for explicit
 * confirmation first. That confirmation was given before this migration was
 * written.
 *
 * Expired members lose read access to announcements entirely (FR-6.5, Schema
 * §5.3). The portal explains that rather than showing an empty list, because
 * an empty list with no explanation reads as a bug (App Flow M-06).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('title', 220);
            $table->text('body');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // content_status is an enum type, which Blueprint cannot express.
        // See the note in the enum migration: announcements inherit a
        // `scheduled` value they have no requirement for.
        DB::statement("ALTER TABLE announcements ADD COLUMN status content_status NOT NULL DEFAULT 'draft'");

        DB::statement('CREATE INDEX announcements_status_published_index ON announcements (status, published_at DESC)');

        // Belt and braces, mirroring posts (Schema §2.6): a published row must
        // carry a publication timestamp, so a stuck job cannot leak an
        // announcement with no date.
        DB::statement(
            "ALTER TABLE announcements ADD CONSTRAINT announcements_published_at_check
             CHECK (status <> 'published' OR published_at IS NOT NULL)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
