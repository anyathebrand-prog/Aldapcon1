<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * leadership_profiles — Schema §2.6. FR-1.3, AC-F1.
 *
 * App Flow P-03: "For a compliance association this page does more persuading
 * than any other." An admin must be able to add, edit, reorder and remove a
 * profile and see it publicly without a deploy (AC-F1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_profiles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->string('position', 150);
            $table->text('bio')->nullable();

            // Nullable: P-03 renders a neutral initial-based placeholder
            // rather than a broken image when there is no photograph.
            $table->foreignId('photo_media_id')->nullable()
                ->constrained('media')->nullOnDelete();

            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestampsTz();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_profiles');
    }
};
