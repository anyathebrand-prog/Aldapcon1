<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * slug_redirects — Schema §2.6. App Flow P-06.
 *
 * A changed slug must 301, or every link the association has ever shared
 * breaks. Cheap insurance against a content editor renaming a post.
 *
 * Audit IG-13: redirects whose target no longer exists are purged on the same
 * cycle as the content, in Phase 14. Otherwise a redirect outlives its target
 * and 301s visitors to a 404, which is worse than no redirect at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slug_redirects', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('entity_type', 30);   // post | event | page
            $table->string('old_slug', 240);
            $table->unsignedBigInteger('entity_id');
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['entity_type', 'old_slug']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slug_redirects');
    }
};
