<?php

declare(strict_types=1);

use App\Domain\Membership\Actions\AllocateMembershipNumber;
use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Support\Facades\DB;

/**
 * The membership number lock, proved without RefreshDatabase — FR-3.11, AC-F3.
 *
 * ── Why this file is outside tests/Feature ───────────────────────────────
 *
 * RefreshDatabase wraps every test in a transaction that is rolled back at the
 * end. That is exactly wrong for these two assertions:
 *
 *   1. AllocateMembershipNumber refuses to run outside a transaction. Under
 *      RefreshDatabase transactionLevel() is never 0, so the guard can never
 *      be exercised — the test would pass without proving anything.
 *
 *   2. A second connection cannot see uncommitted rows. Asking it to contend
 *      for a lock on a counter row that has not been committed means it finds
 *      no row at all, so the lock looks absent whether or not it was taken.
 *
 * Both are the failure mode this test exists to catch: an assertion that
 * passes regardless of the behaviour. So these run against committed data and
 * clean up after themselves.
 *
 * Schema §8.4 rates a membership number collision "unfixable
 * retrospectively" — the number is in the welcome email, in correspondence,
 * and eventually on a certificate. It is worth this much care.
 */
afterEach(function (): void {
    // No transaction to roll back, so the cleanup is explicit. Ordered by
    // dependency: counters reference categories.
    DB::table('membership_number_counters')->delete();
    DB::table('membership_categories')->delete();
});

it('refuses to allocate outside a transaction', function (): void {
    // Outside one the FOR UPDATE lock releases immediately and the guarantee
    // silently disappears. A lock that is not held looks exactly like a lock
    // that is, so the action fails loudly instead.
    $category = MembershipCategory::factory()->create(['number_prefix' => 'NOTX']);

    expect(DB::transactionLevel())->toBe(0);

    expect(fn () => (new AllocateMembershipNumber)($category))
        ->toThrow(RuntimeException::class, 'must run inside a transaction');
});

it('holds a lock on the counter row while allocating', function (): void {
    // AC-F3 — "Two members registering simultaneously receive different
    // membership numbers."
    //
    // Genuine parallelism is not available in one test process, so the
    // mechanism is proved instead: while the first transaction holds the
    // counter, a second connection asking for the same row with NOWAIT must
    // be refused. If the lock were absent it would succeed, read a stale
    // value, and issue a duplicate number.
    $category = MembershipCategory::factory()->create(['number_prefix' => 'LOCK']);

    DB::beginTransaction();

    $first = (new AllocateMembershipNumber)($category);

    $lockWasHeld = false;

    try {
        DB::connection('pgsql_second')->select(
            'SELECT last_number FROM membership_number_counters WHERE category_id = ? FOR UPDATE NOWAIT',
            [$category->getKey()]
        );
    } catch (Throwable $e) {
        // 55P03 lock_not_available.
        $message = strtolower($e->getMessage());
        $lockWasHeld = str_contains($message, '55p03')
            || str_contains($message, 'could not obtain lock');
    }

    DB::commit();
    DB::connection('pgsql_second')->disconnect();

    expect($first)->toEndWith('-00001')
        ->and($lockWasHeld)->toBeTrue(
            'The counter row was not locked during allocation. Concurrent '
            .'activations would read the same value and issue duplicate '
            .'membership numbers (FR-3.11, AC-F3).'
        );
});

it('continues the sequence across separate committed transactions', function (): void {
    // Sequential and never reused, across transaction boundaries — which is
    // what real activations are.
    $category = MembershipCategory::factory()->create(['number_prefix' => 'SEQ']);
    $allocate = new AllocateMembershipNumber;

    $first = DB::transaction(fn (): string => $allocate($category));
    $second = DB::transaction(fn (): string => $allocate($category));

    expect($first)->toEndWith('-00001')
        ->and($second)->toEndWith('-00002');
});
