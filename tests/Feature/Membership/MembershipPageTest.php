<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\MembershipCategory;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/**
 * P-04 membership page and J-01 category selection — FR-1.4, FR-2.1, AC-F2.
 */
it('lists active categories with their fees', function (): void {
    MembershipCategory::factory()->create([
        'name' => 'Individual DPO',
        'annual_fee_kobo' => 2_500_000,
        'eligibility' => 'Open to practising data protection officers.',
    ]);

    get('/membership')
        ->assertOk()
        ->assertSee('Individual DPO')
        // Money in naira with thousands separators (UI brief §5.2).
        ->assertSee('₦25,000')
        ->assertSee('Open to practising data protection officers.');
});

it('hides a deactivated category from the public page', function (): void {
    // AC-F2 — deactivating removes it from the join flow.
    MembershipCategory::factory()->create(['name' => 'Open category']);
    MembershipCategory::factory()->inactive()->create(['name' => 'Closed category']);

    get('/membership')
        ->assertOk()
        ->assertSee('Open category')
        ->assertDontSee('Closed category');
});

it('shows a category benefits list', function (): void {
    MembershipCategory::factory()->create([
        'benefits' => ['Member pricing on events', 'Access to the register'],
    ]);

    get('/membership')
        ->assertOk()
        ->assertSee('Member pricing on events')
        ->assertSee('Access to the register');
});

it('says before payment that a category needs verification', function (): void {
    // Change set 01. Stated on the public page, not discovered at the
    // registration form — an applicant who learns only after paying that a
    // human must approve them has been surprised at the worst moment.
    MembershipCategory::factory()->verifying()->create(['name' => 'Licensed DPCO']);

    get('/membership')
        ->assertOk()
        ->assertSee('NDPC licence number')
        ->assertSee('Membership begins once an administrator has reviewed it.');
});

it('blocks the join flow when no category is active', function (): void {
    // App Flow P-04 — "this is a launch-blocking configuration error", not a
    // normal empty state: the association's primary conversion path is shut.
    get('/membership')
        ->assertOk()
        ->assertSee('Membership opens shortly')
        ->assertSee('Contact the association');
});

it('offers the categories for selection on the join screen', function (): void {
    MembershipCategory::factory()->create(['name' => 'Individual DPO']);

    get('/join')
        ->assertOk()
        ->assertSee('Individual DPO')
        // §6 — a disabled primary action always states what would enable it.
        ->assertSee('Online payment opens when');
});

it('keeps the membership page reachable without an account', function (): void {
    // The top of the funnel. FR-1.4 makes this public, and nothing about it
    // may require logging in.
    MembershipCategory::factory()->create();

    get('/membership')->assertOk();
    get('/join')->assertOk();
});

it('lets an admin manage categories but never a publisher', function (): void {
    // FR-9.7 — categories carry fees, and fees are money. `categories.manage`
    // is not in the Publisher grant set (Schema §2.2).
    seed(RoleSeeder::class);

    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('admin');

    $publisher = User::factory()->withTwoFactor()->create();
    $publisher->assignRole('publisher');

    actingAs($admin)->get('/admin/membership-categories')->assertOk();
    actingAs($publisher)->get('/admin/membership-categories')->assertForbidden();
});
