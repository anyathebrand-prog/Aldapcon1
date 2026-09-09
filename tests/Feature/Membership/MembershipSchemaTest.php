<?php

declare(strict_types=1);

use App\Domain\Identity\Models\ConsentRecord;
use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\Applicant;
use App\Domain\Membership\Models\MemberDocument;
use App\Domain\Membership\Models\Membership;
use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Membership schema guarantees — Schema §2.3, FR-2.1, FR-3.10, FR-3.11,
 * FR-6.1, AC-F2, AC-F3.
 */
it('creates every Phase 6 table', function (string $table): void {
    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    'membership_categories', 'membership_number_counters', 'applicants',
    'memberships', 'member_profiles', 'membership_status_history',
    'renewal_reminders', 'member_documents',
]);

it('defines each membership enum with exactly the approved values', function (string $type, array $expected): void {
    // PostgreSQL cannot drop an enum value, so a mismatch is never a
    // "just edit it" fix. C-14 in particular: membership_status must NOT
    // gain pending_verification — a pending applicant has no membership row.
    $labels = DB::table('pg_enum')
        ->join('pg_type', 'pg_type.oid', '=', 'pg_enum.enumtypid')
        ->where('pg_type.typname', $type)
        ->orderBy('pg_enum.enumsortorder')
        ->pluck('pg_enum.enumlabel')
        ->all();

    expect($labels)->toBe($expected);
})->with([
    ['applicant_type', ['individual', 'organisation']],
    ['applicant_status', ['initiated', 'paid', 'pending_verification', 'registered', 'rejected', 'abandoned', 'refunded']],
    ['membership_status', ['active', 'expiring_soon', 'expired', 'suspended']],
    ['review_decision', ['approved', 'rejected', 'correction_requested']],
    ['document_type', ['ndpc_certificate']],
    ['reminder_stage', ['t_minus_30', 't_minus_7', 't_minus_1', 't_plus_1']],
]);

it('allows only one live membership per user', function (): void {
    // FR-3.10, the database half. The application checks at J-02 before
    // Paystack is called; reaching this means somebody has paid for nothing.
    $user = User::factory()->create();
    Membership::factory()->create(['user_id' => $user->id]);

    expect(fn () => Membership::factory()->create(['user_id' => $user->id]))
        ->toThrow(QueryException::class);
});

it('lets a user rejoin after their membership is soft deleted', function (): void {
    // The unique index is partial. A former member must be able to come back.
    $user = User::factory()->create();
    $first = Membership::factory()->create(['user_id' => $user->id]);

    $first->delete();

    expect(fn () => Membership::factory()->create(['user_id' => $user->id]))
        ->not->toThrow(QueryException::class);
});

it('refuses a membership that expires before it began', function (): void {
    expect(fn () => Membership::factory()->create([
        'joined_at' => now(),
        'expires_at' => now()->subDay(),
    ]))->toThrow(QueryException::class);
});

it('allows only one in-flight application per email', function (): void {
    // App Flow J-02 — an existing paid-but-unregistered applicant must not be
    // charged twice; the flow offers to resend their link instead.
    Applicant::factory()->paid()->create(['email' => 'ada@example.com']);

    expect(fn () => Applicant::factory()->create(['email' => 'ada@example.com']))
        ->toThrow(QueryException::class);
});

it('lets a rejected applicant apply again', function (): void {
    // The index covers only live states. A refusal is not a permanent ban.
    $user = User::factory()->create();

    Applicant::factory()->create([
        'email' => 'ada@example.com',
        'status' => 'rejected',
        'user_id' => $user->id,
        'reviewed_at' => now(),
        'review_reason' => 'The certificate was for a different organisation.',
    ]);

    expect(fn () => Applicant::factory()->create(['email' => 'ada@example.com']))
        ->not->toThrow(QueryException::class);
});

it('refuses a rejection with no reason', function (): void {
    // FR-3.13.4 — rejection requires a reason, enforced at the database so no
    // code path can refuse somebody silently.
    $user = User::factory()->create();

    expect(fn () => Applicant::factory()->create([
        'status' => 'rejected',
        'user_id' => $user->id,
        'reviewed_at' => now(),
        'review_reason' => null,
    ]))->toThrow(QueryException::class);
});

it('refuses a paid applicant with no payment timestamp', function (): void {
    expect(fn () => Applicant::factory()->create([
        'status' => 'paid',
        'paid_at' => null,
    ]))->toThrow(QueryException::class);
});

it('refuses a certificate with a disallowed mime type', function (): void {
    // FR-3.6 — PDF, JPG, PNG. At the database as well as in validation,
    // because this is the only file upload surface in the product (TRD §11.7).
    $applicant = Applicant::factory()->create();

    expect(fn () => MemberDocument::factory()->create([
        'applicant_id' => $applicant->id,
        'mime_type' => 'application/x-httpd-php',
    ]))->toThrow(QueryException::class);
});

it('accepts the three permitted certificate formats', function (string $mime): void {
    $applicant = Applicant::factory()->create();

    $document = MemberDocument::factory()->create([
        'applicant_id' => $applicant->id,
        'mime_type' => $mime,
        'path' => 'certificates/'.Str::uuid().'/'.Str::uuid().'.bin',
    ]);

    expect($document->mime_type)->toBe($mime);
})->with(['application/pdf', 'image/jpeg', 'image/png']);

it('links a consent record to an applicant', function (): void {
    // The foreign key deferred from Phase 3 (audit IG-1), closed in this
    // phase now that `applicants` exists.
    $applicant = Applicant::factory()->create();

    $consent = ConsentRecord::factory()->create(['applicant_id' => $applicant->id]);

    expect($consent->applicant_id)->toBe($applicant->id);
});

it('refuses a consent record pointing at an applicant that does not exist', function (): void {
    // Proves the constraint is actually present, not just the column.
    expect(fn () => ConsentRecord::factory()->create(['applicant_id' => 999999]))
        ->toThrow(QueryException::class);
});

it('keeps a fee change away from historical records', function (): void {
    // AC-F2 — "Editing a fee changes the price for new payments only."
    // Payments snapshot their own amount (Phase 8); an applicant snapshots the
    // amount quoted at initiation.
    $category = MembershipCategory::factory()->create(['annual_fee_kobo' => 2_500_000]);
    $applicant = Applicant::factory()->create([
        'category_id' => $category->id,
        'fee_kobo_at_initiation' => 2_500_000,
    ]);

    $category->update(['annual_fee_kobo' => 4_000_000]);

    expect($applicant->fresh()?->fee_kobo_at_initiation)->toBe(2_500_000)
        ->and($category->fresh()?->annual_fee_kobo)->toBe(4_000_000);
});

it('preserves members when a category is deactivated', function (): void {
    // AC-F2 — "Deactivating a category removes it from the public join flow
    // but preserves existing members in it."
    $category = MembershipCategory::factory()->create();
    $membership = Membership::factory()->create(['category_id' => $category->id]);

    $category->update(['is_active' => false]);

    expect($membership->fresh())->not->toBeNull()
        ->and($membership->fresh()?->category_id)->toBe($category->id);
});

it('refuses to delete a category that has members', function (): void {
    // ON DELETE RESTRICT. Deleting would orphan them.
    $category = MembershipCategory::factory()->create();
    Membership::factory()->create(['category_id' => $category->id]);

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});

it('sends each renewal reminder once per cycle', function (): void {
    // Schema §2.3 — keying on (membership_id, stage) alone would send the
    // 30-day reminder once in a member's lifetime and never again after their
    // first renewal, "a bug that would not surface until year two".
    $membership = Membership::factory()->create();
    $cycle = $membership->expires_at;

    DB::table('renewal_reminders')->insert([
        'membership_id' => $membership->id,
        'cycle_expires_at' => $cycle,
        'stage' => 't_minus_30',
        'sent_at' => now(),
    ]);

    expect(fn () => DB::table('renewal_reminders')->insert([
        'membership_id' => $membership->id,
        'cycle_expires_at' => $cycle,
        'stage' => 't_minus_30',
        'sent_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('reminds again in the next cycle', function (): void {
    // The year-two bug the cycle key prevents.
    $membership = Membership::factory()->create();

    DB::table('renewal_reminders')->insert([
        'membership_id' => $membership->id,
        'cycle_expires_at' => $membership->expires_at,
        'stage' => 't_minus_30',
        'sent_at' => now(),
    ]);

    expect(fn () => DB::table('renewal_reminders')->insert([
        'membership_id' => $membership->id,
        'cycle_expires_at' => $membership->expires_at->copy()->addYear(),
        'stage' => 't_minus_30',
        'sent_at' => now(),
    ]))->not->toThrow(QueryException::class);
});

it('derives status from the expiry date', function (): void {
    // FR-6.2 — Active → Expiring soon within 30 days → Expired.
    // The full test-clock sweep at each boundary belongs to Phase 11.
    expect(Membership::factory()->make(['expires_at' => now()->addMonths(6)])->derivedStatus())->toBe('active')
        ->and(Membership::factory()->make(['expires_at' => now()->addDays(20)])->derivedStatus())->toBe('expiring_soon')
        ->and(Membership::factory()->make(['expires_at' => now()->subDay()])->derivedStatus())->toBe('expired');
});

it('never lets the scheduler overwrite a suspension', function (): void {
    // Suspension is an administrative decision, not a date.
    $suspended = Membership::factory()->make([
        'status' => 'suspended',
        'expires_at' => now()->addMonths(6),
    ]);

    expect($suspended->derivedStatus())->toBe('suspended');
});

it('lets expired members keep their account but not their privileges', function (): void {
    // FR-6.5 — expiry restricts what they can do, never whether they can log
    // in. Locking them out would remove the one screen that offers renewal.
    expect(Membership::factory()->make(['status' => 'active'])->hasMemberPrivileges())->toBeTrue()
        ->and(Membership::factory()->make(['status' => 'expiring_soon'])->hasMemberPrivileges())->toBeTrue()
        ->and(Membership::factory()->make(['status' => 'expired'])->hasMemberPrivileges())->toBeFalse()
        ->and(Membership::factory()->make(['status' => 'suspended'])->hasMemberPrivileges())->toBeFalse();
});
