<?php

declare(strict_types=1);

use App\Domain\Membership\Actions\AllocateMembershipNumber;
use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Support\Facades\DB;

/**
 * Membership number allocation — FR-3.11, AC-F3, Schema §2.3, §8.4.
 *
 * "Membership numbers must be unique, sequential per category, and never
 * reused."
 *
 * The plan names the concurrency case as the test that matters most in this
 * phase, and Schema §8.4 rates a collision as unfixable retrospectively: the
 * number is in the welcome email, in correspondence, and eventually on
 * certificates. Two members sharing one means telling somebody their number
 * has changed, at an association whose product is being a reliable register.
 */
it('allocates sequentially within a category', function (): void {
    $category = MembershipCategory::factory()->create(['number_prefix' => 'DPO']);
    $allocate = new AllocateMembershipNumber;

    $numbers = DB::transaction(fn (): array => [
        $allocate($category),
        $allocate($category),
        $allocate($category),
    ]);

    $year = now()->timezone('Africa/Lagos')->format('Y');

    expect($numbers)->toBe([
        "DPO-{$year}-00001",
        "DPO-{$year}-00002",
        "DPO-{$year}-00003",
    ]);
});

it('numbers each category independently', function (): void {
    // Sequential PER CATEGORY (FR-3.11). A shared sequence would make the
    // number say nothing about which category issued it.
    $dpo = MembershipCategory::factory()->create(['number_prefix' => 'DPO']);
    $dpco = MembershipCategory::factory()->create(['number_prefix' => 'DPCO']);
    $allocate = new AllocateMembershipNumber;

    [$first, $second] = DB::transaction(fn (): array => [
        $allocate($dpo),
        $allocate($dpco),
    ]);

    $year = now()->timezone('Africa/Lagos')->format('Y');

    expect($first)->toBe("DPO-{$year}-00001")
        ->and($second)->toBe("DPCO-{$year}-00001");
});

it('pads the number to five digits', function (): void {
    $category = MembershipCategory::factory()->create(['number_prefix' => 'DPCO']);

    DB::table('membership_number_counters')
        ->where('category_id', $category->getKey())
        ->update(['last_number' => 33]);

    $number = DB::transaction(fn (): string => (new AllocateMembershipNumber)($category));

    expect($number)->toBe('DPCO-'.now()->timezone('Africa/Lagos')->format('Y').'-00034');
});

it('refuses to run outside a transaction', function (): void {
    // Outside one, FOR UPDATE releases immediately and the guarantee silently
    // disappears. Failing loudly is the only honest option: a lock that is
    // not held looks exactly like a lock that is.
    $category = MembershipCategory::factory()->create();

    expect(fn () => (new AllocateMembershipNumber)($category))
        ->toThrow(RuntimeException::class, 'must run inside a transaction');
});

it('creates a counter with every category', function (): void {
    // A category without a counter cannot issue numbers, and the failure
    // would surface at activation — after somebody had paid.
    $category = MembershipCategory::factory()->create();

    expect(
        DB::table('membership_number_counters')->where('category_id', $category->getKey())->exists()
    )->toBeTrue();
});

it('never issues the same number twice under concurrent allocation', function (): void {
    // AC-F3 — "Two members registering simultaneously receive different
    // membership numbers."
    //
    // Genuine parallelism is not available in a single test process, so this
    // proves the mechanism instead: a second connection attempting to read the
    // counter while the first holds the lock must WAIT rather than read a
    // stale value.
    //
    // NOWAIT turns that wait into an immediate error, which is what makes the
    // lock observable. If the lock were not held, this would succeed and the
    // test would fail — which is the case that produces duplicate numbers in
    // production.
    $category = MembershipCategory::factory()->create(['number_prefix' => 'DPCO']);

    DB::beginTransaction();

    // Take the lock, as an activation transaction would.
    (new AllocateMembershipNumber)($category);

    $secondConnection = DB::connection('pgsql_second');

    $lockWasHeld = false;

    try {
        $secondConnection->select(
            'SELECT last_number FROM membership_number_counters WHERE category_id = ? FOR UPDATE NOWAIT',
            [$category->getKey()]
        );
    } catch (Throwable $e) {
        // 55P03 lock_not_available — the row is locked, which is the point.
        $lockWasHeld = str_contains($e->getMessage(), '55P03')
            || str_contains(strtolower($e->getMessage()), 'could not obtain lock');
    }

    DB::rollBack();
    $secondConnection->disconnect();

    expect($lockWasHeld)->toBeTrue(
        'The counter row was not locked during allocation. Concurrent activations '
        .'would read the same value and issue duplicate membership numbers (AC-F3).'
    );
});
