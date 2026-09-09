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

/*
 * MOVED to tests/Concurrency/MembershipNumberLockTest.php.
 *
 * The transaction guard and the lock contention cannot be proved under
 * RefreshDatabase: it already holds a transaction, and a second connection
 * cannot see uncommitted rows. Both assertions would have passed whether or
 * not the behaviour was present.
 */
it('creates a counter with every category', function (): void {
    // A category without a counter cannot issue numbers, and the failure
    // would surface at activation — after somebody had paid.
    $category = MembershipCategory::factory()->create();

    expect(
        DB::table('membership_number_counters')->where('category_id', $category->getKey())->exists()
    )->toBeTrue();
});
