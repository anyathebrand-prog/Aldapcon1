<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Membership enum types — Schema §2.3, change set 01.
 *
 * PERMANENT. PostgreSQL cannot drop a value from an enum, so every value here
 * is written for the life of the database. The plan gates this phase on B-2
 * precisely so these are correct the first time rather than retrofitted into a
 * live lifecycle.
 *
 * ── applicant_status carries the verification path (change set 01) ────────
 *
 * initiated  → created at J-02, before payment
 * paid       → payment verified, registration not yet completed (FR-3.8)
 * pending_verification → registered, awaiting an administrator decision
 * registered → membership exists
 * rejected   → refused, with a reason (FR-3.13.4)
 * abandoned  → never registered (PRD Q4 — still unanswered, see below)
 * refunded   → money returned
 *
 * ── membership_status is deliberately UNCHANGED (audit C-14) ─────────────
 *
 * An earlier draft added `pending_verification` here. It is on applicant_status
 * instead, and no membership row exists until approval. A pending applicant is
 * not a member: keeping them out of `memberships` preserves
 * membership_number NOT NULL, leaves every dashboard count untouched, and
 * keeps the one-live-membership-per-user constraint meaningful.
 *
 * ── What is NOT here ────────────────────────────────────────────────────
 *
 * `abandoned` and `refunded` exist on applicant_status but have no transition
 * into them yet, because PRD Q4 — what happens to somebody who pays and never
 * registers — is unanswered (plan C-10). The values are written now because
 * enums cannot be extended without a migration and the schema names them; the
 * transitions arrive with the policy.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $types = [
        'applicant_type' => ['individual', 'organisation'],
        'applicant_status' => [
            'initiated', 'paid', 'pending_verification',
            'registered', 'rejected', 'abandoned', 'refunded',
        ],
        'membership_status' => ['active', 'expiring_soon', 'expired', 'suspended'],
        'review_decision' => ['approved', 'rejected', 'correction_requested'],
        'document_type' => ['ndpc_certificate'],
        'reminder_stage' => ['t_minus_30', 't_minus_7', 't_minus_1', 't_plus_1'],
    ];

    public function up(): void
    {
        foreach ($this->types as $name => $values) {
            $quoted = implode(', ', array_map(fn (string $v): string => "'".$v."'", $values));

            // PostgreSQL has no CREATE TYPE IF NOT EXISTS, and migrate:fresh
            // drops TABLES while leaving TYPES behind. Same guard as Phase 3.
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '{$name}') THEN
                        CREATE TYPE {$name} AS ENUM ({$quoted});
                    END IF;
                END
                $$;
            ");
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys($this->types)) as $name) {
            DB::statement("DROP TYPE IF EXISTS {$name}");
        }
    }
};
