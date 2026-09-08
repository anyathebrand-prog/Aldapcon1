<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * pages — Schema §2.6. FR-1.1, FR-12.4, NFR 6.6.
 *
 * About, Contact and the legal pages. `is_system` marks the six the navigation
 * and signup flow link to by slug: they are editable but not deletable, since
 * deleting one breaks a hard-coded link in the join flow.
 *
 * search_vector is a GENERATED column, not maintained by application code.
 * FR-1.6 site search reads it directly through PostgreSQL full-text — no
 * Scout, per audit IG-2, because Scout's database driver performs LIKE
 * matching against model attributes and would never read this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('title', 220);
            $table->string('slug', 240)->unique();
            $table->text('body');
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('updated_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        // Generated and stored, so it can never drift from the row it
        // describes. Weighting title above body means a page whose TITLE
        // matches outranks one that mentions the term in passing.
        DB::statement("
            ALTER TABLE pages ADD COLUMN search_vector TSVECTOR
            GENERATED ALWAYS AS (
                setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(body, '')), 'B')
            ) STORED
        ");

        DB::statement('CREATE INDEX pages_search_vector_index ON pages USING GIN (search_vector)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
