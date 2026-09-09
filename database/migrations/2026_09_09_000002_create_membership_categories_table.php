<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * membership_categories and membership_number_counters — Schema §2.3.
 * FR-2.1, FR-2.2, FR-3.11, AC-F2, AC-F3.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_categories', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->string('slug', 140)->unique();   // /join/{category}
            $table->text('eligibility');
            $table->jsonb('benefits')->default('[]');

            /*
             * Money is BIGINT kobo (plan §2 rule 1). 2500000 is ₦25,000.
             * No floats, ever — and the column is suffixed so the unit is
             * impossible to misread at a call site.
             *
             * This is the CURRENT price. payments.amount_kobo snapshots what
             * was actually charged, so changing a fee here can never alter a
             * receipt issued last year (AC-F2).
             */
            $table->bigInteger('annual_fee_kobo');
            $table->char('currency', 3)->default('NGN');

            /*
             * Change set 01, renamed from requires_licence_number by audit N-3.
             *
             * When true the category requires a licence number, a certificate
             * upload, and an administrator's approval before any membership is
             * created. When false the membership activates immediately on
             * registration (FR-3.7).
             *
             * Per category, not per product: individual DPO categories keep
             * the fast path.
             */
            $table->boolean('requires_verification')->default(false);

            $table->boolean('is_active')->default(true);

            // Feeds membership numbers: DPCO-2026-00034 (Schema §2.3).
            $table->string('number_prefix', 12)->unique();

            $table->smallInteger('sort_order')->default(0);
            $table->timestampsTz();

            $table->index(['is_active', 'sort_order']);
        });

        DB::statement('ALTER TABLE membership_categories ADD COLUMN applicant_type applicant_type NOT NULL');
        DB::statement('ALTER TABLE membership_categories ADD CONSTRAINT membership_categories_fee_check CHECK (annual_fee_kobo >= 0)');

        /*
         * The number allocator — FR-3.11, AC-F3.
         *
         * One row per category, locked with SELECT ... FOR UPDATE inside the
         * activation transaction.
         *
         * Schema §2.3 is blunt about the alternative: "Application-level
         * MAX(number) + 1 fails the concurrent-activation case in AC-F3 and
         * will eventually issue duplicates." Two members sharing a membership
         * number is not fixable retrospectively — the numbers are on
         * certificates and in correspondence.
         *
         * A counter table rather than a Postgres sequence per category,
         * because creating a category should not require DDL.
         */
        Schema::create('membership_number_counters', function (Blueprint $table): void {
            $table->foreignId('category_id')->primary()
                ->constrained('membership_categories')->cascadeOnDelete();
            $table->integer('last_number')->default(0);
            $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement('ALTER TABLE membership_number_counters ADD CONSTRAINT counters_last_number_check CHECK (last_number >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_number_counters');
        Schema::dropIfExists('membership_categories');
    }
};
