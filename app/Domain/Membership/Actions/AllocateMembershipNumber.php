<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Allocates the next membership number — FR-3.11, AC-F3, Schema §2.3.
 *
 * "Membership numbers must be unique, sequential per category, and never
 * reused."
 *
 * ── Why this is not MAX(number) + 1 ──────────────────────────────────────
 *
 * Schema §2.3 is explicit: "Application-level MAX(number) + 1 fails the
 * concurrent-activation case in AC-F3 and will eventually issue duplicates."
 *
 * Two members sharing a membership number is not fixable afterwards. The
 * number goes in the welcome email, on correspondence, and eventually on
 * certificates. Discovering the collision means telling one of them their
 * number has changed, at an association whose entire product is being a
 * reliable register.
 *
 * So the counter row is locked with SELECT ... FOR UPDATE. A second concurrent
 * allocation for the same category waits for the first to commit rather than
 * reading a stale value. AC-F3 tests exactly this: "Two members registering
 * simultaneously receive different membership numbers."
 *
 * ── Must be called inside a transaction ──────────────────────────────────
 *
 * The lock is held until commit. Called outside one, FOR UPDATE releases
 * immediately and the guarantee silently disappears — so this refuses to run
 * rather than appearing to work.
 *
 * ── When it is called ────────────────────────────────────────────────────
 *
 * At the point membership is CREATED. For a verifying category that is
 * APPROVAL, not registration (Schema §4.3b), so a rejected application
 * consumes no number. Allocating at registration would burn numbers on
 * applicants who are later refused, and since numbers are never reused the
 * sequence would develop permanent gaps an auditor would eventually ask about.
 */
final class AllocateMembershipNumber
{
    public function __invoke(MembershipCategory $category): string
    {
        if (DB::transactionLevel() === 0) {
            throw new RuntimeException(
                'AllocateMembershipNumber must run inside a transaction. '
                .'Outside one the FOR UPDATE lock releases immediately and '
                .'concurrent activations can receive the same number (FR-3.11).'
            );
        }

        // Locks the counter row for this category until the surrounding
        // transaction commits. Other categories are unaffected — allocation
        // is serialised per category, not globally.
        $current = DB::table('membership_number_counters')
            ->where('category_id', $category->getKey())
            ->lockForUpdate()
            ->value('last_number');

        if ($current === null) {
            throw new RuntimeException(
                "No membership number counter exists for category {$category->getKey()}. "
                .'A category without a counter cannot issue numbers; one is created '
                .'with every category.'
            );
        }

        $next = ((int) $current) + 1;

        DB::table('membership_number_counters')
            ->where('category_id', $category->getKey())
            ->update(['last_number' => $next, 'updated_at' => now()]);

        // {prefix}-{year}-{number padded to 5} → DPCO-2026-00034 (Schema §2.3).
        //
        // The year is the year of issue and is part of the identifier, not a
        // lookup: it never changes for a member, and the counter does not
        // reset when the year does. Resetting would break "never reused".
        return sprintf(
            '%s-%s-%05d',
            $category->number_prefix,
            now()->timezone('Africa/Lagos')->format('Y'),
            $next
        );
    }
}
