<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * posts, content_terms, post_term — Schema §2.6. FR-7.1 to FR-7.4, FR-1.6.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('title', 220);
            $table->string('slug', 240)->unique();
            $table->string('excerpt', 400)->nullable();
            $table->text('body');

            $table->foreignId('featured_media_id')->nullable()
                ->constrained('media')->nullOnDelete();

            // RESTRICT, not cascade: deleting an author must not silently
            // delete the association's published history.
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();

            $table->timestampTz('published_at')->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('author_user_id');
        });

        DB::statement("ALTER TABLE posts ADD COLUMN status content_status NOT NULL DEFAULT 'draft'");

        DB::statement('CREATE INDEX posts_status_published_index ON posts (status, published_at DESC)');

        /*
         * FR-7.2 scheduling, and AC-F7's "drafts are not publicly accessible
         * by URL".
         *
         * A published row must carry a date. Public queries then filter on
         * status = published AND published_at <= now(), so a stuck scheduler
         * cannot leak a post early and a draft URL 404s. Belt and braces, as
         * the schema puts it — the constraint and the query each catch what
         * the other might miss.
         */
        DB::statement(
            "ALTER TABLE posts ADD CONSTRAINT posts_published_at_check
             CHECK (status <> 'published' OR published_at IS NOT NULL)"
        );

        DB::statement("
            ALTER TABLE posts ADD COLUMN search_vector TSVECTOR
            GENERATED ALWAYS AS (
                setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(excerpt, '')), 'B') ||
                setweight(to_tsvector('english', coalesce(body, '')), 'C')
            ) STORED
        ");

        DB::statement('CREATE INDEX posts_search_vector_index ON posts USING GIN (search_vector)');

        Schema::create('content_terms', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('type', 20);   // category | tag
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->timestampsTz();

            $table->unique(['type', 'slug']);
        });

        Schema::create('post_term', function (Blueprint $table): void {
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('content_terms')->cascadeOnDelete();

            $table->primary(['post_id', 'term_id']);
            $table->index('term_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_term');
        Schema::dropIfExists('content_terms');
        Schema::dropIfExists('posts');
    }
};
