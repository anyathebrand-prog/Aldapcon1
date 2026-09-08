<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * faqs — Schema §2.6. FR-1.1, FR-1.6.
 *
 * `group` is quoted throughout because GROUP is a reserved word in SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('question', 400);
            $table->text('answer');
            $table->string('group', 80)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestampsTz();

            $table->index(['is_published', 'sort_order']);
        });

        DB::statement("
            ALTER TABLE faqs ADD COLUMN search_vector TSVECTOR
            GENERATED ALWAYS AS (
                setweight(to_tsvector('english', coalesce(question, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(answer, '')), 'B')
            ) STORED
        ");

        DB::statement('CREATE INDEX faqs_search_vector_index ON faqs USING GIN (search_vector)');
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
