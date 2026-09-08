<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * settings — Schema §2.8. NFR 6.6, screen D-21.
 *
 * Association contact details, social links, sender configuration, renewal
 * reminder timing, cookie banner text, policy version pointers. Anything a
 * non-technical administrator must be able to change without a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->string('key', 80)->primary();
            $table->jsonb('value');
            $table->string('group', 40);
            $table->foreignId('updated_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
