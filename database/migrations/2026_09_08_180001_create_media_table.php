<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * media — Schema §2.6. FR-7.1, FR-8.1, NFR 6.5.
 *
 * Publicly served content images only: post and event artwork, leadership
 * photographs. NDPC certificates are deliberately NOT stored here — they get
 * their own table on the private disk in Phase 6, because they need different
 * access rules, different retention and integrity hashing (Schema §2.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('disk', 30)->default('local');
            $table->string('path', 400)->unique();
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            // NOT NULL at the database level, deliberately.
            //
            // NFR 6.5 requires alt text as a validation rule. Enforcing it in
            // the schema means no code path can bypass it — not a seeder, not
            // a console command, not a future import. A validation rule can be
            // forgotten in one controller; a NOT NULL cannot.
            $table->string('alt_text', 300);

            $table->foreignId('uploaded_by_user_id')->constrained('users');
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
