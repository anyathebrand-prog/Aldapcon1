<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * password_reset_tokens — Schema §2.1. FR-4.2, AC-F4.
 *
 * Laravel standard. The token is stored hashed; the plaintext lives only in
 * the email. Rows are deleted on use, which is what makes a reset link
 * single-use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        DB::statement('ALTER TABLE password_reset_tokens ALTER COLUMN email TYPE CITEXT');
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
