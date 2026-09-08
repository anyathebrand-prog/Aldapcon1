<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * policy_versions — Schema §2.8. FR-12.2, FR-12.4.
 *
 * Exists so a consent record points at a policy somebody can still read. App
 * Flow P-12: "a consent record pointing at a version nobody can read is not
 * evidence of anything."
 *
 * ROWS ARE NEVER UPDATED OR DELETED. A new policy is a new row. That is not a
 * convention — it is the whole reason the table exists.
 *
 * Seeding at least one version of each policy is launch-blocking (Schema
 * §8.2): consent cannot be recorded without one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_versions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('slug', 40);      // privacy-policy | cookie-policy | terms
            $table->string('version', 20);
            $table->text('body');
            $table->timestampTz('effective_from');
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['slug', 'version']);
            $table->index(['slug', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_versions');
    }
};
