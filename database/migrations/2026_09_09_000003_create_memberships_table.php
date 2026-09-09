<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * memberships — Schema §2.3. FR-3.7, FR-3.10, FR-3.11, FR-5.1, FR-6.1 to 6.6.
 *
 * Created BEFORE applicants, because applicants.membership_id points here.
 * The reverse link (applicants → membership) is what makes "this payment
 * became that membership" traceable without re-pointing the payment row —
 * see audit BC-2, which removed exactly that re-pointing.
 *
 * A row exists here only for a real member. A pending applicant has none
 * (audit C-14), which is what keeps membership_number NOT NULL honest and
 * every dashboard count correct.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            // RESTRICT on both: a member with a membership cannot be deleted,
            // they are anonymised (Schema §6.3). Financial history survives
            // the person being erased.
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('membership_categories')->restrictOnDelete();

            // NOT NULL, and never reused (FR-3.11). Allocated at the point
            // membership is created — which for a verifying category is
            // APPROVAL, not registration, so a rejected application consumes
            // no number.
            $table->string('membership_number', 30)->unique();

            $table->timestampTz('joined_at');

            /*
             * End of day, Africa/Lagos (Schema §1.4).
             *
             * "expires on 14 March" must not mean "expired at 1am on the
             * 14th" for a member in Lagos. Stored UTC; the end-of-day offset
             * is applied when the value is set.
             */
            $table->timestampTz('expires_at');

            $table->timestampTz('last_renewed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_id');
        });

        DB::statement("ALTER TABLE memberships ADD COLUMN status membership_status NOT NULL DEFAULT 'active'");

        // Indexes on `status` are created after the column, not in the
        // Blueprint above — Laravel has no enum-type column, so the column
        // does not exist until the ALTER runs.
        //
        // These two drive the daily lifecycle job and the D-01 dashboard
        // counts, which are the only queries that run over every membership.
        DB::statement('CREATE INDEX memberships_status_expires_index ON memberships (status, expires_at)');
        DB::statement('CREATE INDEX memberships_category_status_index ON memberships (category_id, status)');

        /*
         * FR-3.10, the database half — Schema §2.3.
         *
         * One LIVE membership per user. The application checks at J-02 before
         * Paystack is called (C-1); this is the safety net, and reaching it
         * means the application already failed and somebody has paid for
         * nothing.
         *
         * Partial, so a soft-deleted membership does not block a rejoin.
         */
        DB::statement(
            'CREATE UNIQUE INDEX memberships_one_live_per_user
             ON memberships (user_id) WHERE deleted_at IS NULL'
        );

        DB::statement(
            'ALTER TABLE memberships ADD CONSTRAINT memberships_expiry_after_join_check
             CHECK (expires_at > joined_at)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
