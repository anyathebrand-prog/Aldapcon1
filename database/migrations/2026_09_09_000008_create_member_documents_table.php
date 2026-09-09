<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * member_documents — Schema §2.3, change set 01. FR-3.6, FR-3.13, TRD §11.7.
 *
 * NDPC certificates uploaded for verification.
 *
 * ── Deliberately NOT stored in `media` ───────────────────────────────────
 *
 * `media` is built for publicly served content images and requires
 * alt_text NOT NULL, which is meaningless for a licence certificate. Private
 * documents need different access rules, different retention and integrity
 * hashing. Sharing the table would mean one careless join away from a
 * certificate appearing in a public image URL.
 *
 * ── This is the only file upload in the member-facing product ────────────
 *
 * TRD §11.7: it is therefore "the only path by which an outside party can
 * place a file on the server, and the natural first target for anybody probing
 * the application."
 *
 * The database half of the mitigation is here — a MIME allow-list as a CHECK
 * constraint, a size column, and a sha256. The rest (extension allow-list,
 * re-encoding, storage outside the web root, authenticated streaming) is
 * Phase 9b and is hardened again in Phase 16.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_documents', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            // RESTRICT: the document is evidence of a decision about this
            // applicant and must not vanish with them.
            $table->foreignId('applicant_id')->constrained('applicants')->restrictOnDelete();

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->string('disk', 30)->default('private');

            // storage/app/private/certificates/{applicant_uuid}/{document_uuid}.{ext}
            // Never under public/, never symlinked (Schema §6.1).
            $table->string('path', 400)->unique();

            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');

            /*
             * Lets an administrator confirm the document reviewed is the
             * document stored — "worth one column for a body whose entire
             * product is verification" (Schema §2.3).
             */
            $table->char('sha256', 64);

            // Preserves the original when a correction is uploaded
            // (FR-3.13.5), so the trail of what was submitted survives.
            $table->foreignId('superseded_by_id')->nullable()
                ->constrained('member_documents')->nullOnDelete();

            $table->timestampTz('uploaded_at')->useCurrent();
            $table->softDeletesTz();

            $table->index('deleted_at');
        });

        DB::statement('ALTER TABLE member_documents ADD COLUMN type document_type NOT NULL');
        DB::statement('CREATE INDEX member_documents_applicant_type_index ON member_documents (applicant_id, type)');

        DB::statement('ALTER TABLE member_documents ADD CONSTRAINT member_documents_size_check CHECK (size_bytes > 0)');

        /*
         * FR-3.6 — "Accepted formats: PDF, JPG, PNG."
         *
         * At the database, not only in validation. Application validation is
         * the first line and this is the last: on the single upload surface
         * in the product, a bypassed validator should still not be able to
         * record an executable as a certificate.
         */
        DB::statement(
            "ALTER TABLE member_documents ADD CONSTRAINT member_documents_mime_check
             CHECK (mime_type IN ('application/pdf', 'image/jpeg', 'image/png'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('member_documents');
    }
};
