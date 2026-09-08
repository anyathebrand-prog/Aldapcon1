<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\seed;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles and permissions — FR-9.7, AC-F9, Schema §2.2.
 *
 * Plan Phase 3: "Role and permission seeding produces exactly the sets in
 * Schema §2.2."
 *
 * The Publisher assertions are the ones that matter. AC-F9 requires a
 * Publisher to be refused member and payment data by direct URL, and the way
 * that is guaranteed is their permission set containing no grant that touches
 * those tables — not by hiding menu items, which is not authorisation
 * (plan §2 rule 6).
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
});

it('creates exactly four roles', function (): void {
    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'member', 'publisher', 'super_admin']);
});

it('creates every permission in the schema', function (): void {
    expect(Permission::query()->count())->toBe(count(RoleSeeder::PERMISSIONS));
});

it('gives super admin everything', function (): void {
    $role = Role::findByName('super_admin');

    expect($role->permissions)->toHaveCount(count(RoleSeeder::PERMISSIONS));
});

it('withholds the four super admin reserves from admin', function (string $permission): void {
    // members.delete because deletion interacts with financial retention
    // (Schema §6.3); audit.view because "a log an actor can read is a log they
    // can plan around" (§5.3 rule 3).
    expect(Role::findByName('admin')->hasPermissionTo($permission))->toBeFalse();
})->with(['users.manage', 'audit.view', 'settings.manage', 'members.delete']);

it('gives admin the verification permissions', function (string $permission): void {
    // Change set 01 — an Admin decides applications; the queue is theirs.
    expect(Role::findByName('admin')->hasPermissionTo($permission))->toBeTrue();
})->with(['verifications.view', 'verifications.decide', 'documents.view']);

it('gives publisher content and events only', function (): void {
    expect(Role::findByName('publisher')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['content.manage', 'events.manage']);
});

it('never lets a publisher reach member, payment or certificate data', function (string $permission): void {
    // AC-F9. This is the single most important assertion in the phase: the
    // grant simply does not exist, so there is nothing for a controller to
    // get wrong.
    expect(Role::findByName('publisher')->hasPermissionTo($permission))->toBeFalse();
})->with([
    'members.view', 'members.update', 'members.export', 'members.delete',
    'payments.view', 'payments.export', 'refunds.record',
    'applicants.view',
    'verifications.view', 'verifications.decide', 'documents.view',
    'data_requests.manage',
]);

it('gives the member role no administrative permission at all', function (): void {
    // FR-9.7, audit IG-12. Portal access is by record ownership, not by
    // permission (Schema §5.1) — a member reaches their own rows through the
    // authenticated user, never through a parameter.
    expect(Role::findByName('member')->permissions)->toBeEmpty();
});

it('assigns a role to a user', function (): void {
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasPermissionTo('members.view'))->toBeTrue()
        ->and($user->hasPermissionTo('audit.view'))->toBeFalse();
});

it('requires two factor for staff but not for members', function (): void {
    // FR-4.3 — mandatory for Super Admin and Admin. Publisher is excluded
    // because their permission set reaches no member, payment or certificate
    // data, so a second factor would protect nothing.
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $publisher = User::factory()->create();
    $publisher->assignRole('publisher');

    $member = User::factory()->create();
    $member->assignRole('member');

    expect($superAdmin->requiresTwoFactor())->toBeTrue()
        ->and($admin->requiresTwoFactor())->toBeTrue()
        ->and($publisher->requiresTwoFactor())->toBeFalse()
        ->and($member->requiresTwoFactor())->toBeFalse();
});

it('is idempotent', function (): void {
    // Seeders run on every deploy. Running twice must not duplicate a
    // permission or silently drop a grant.
    seed(RoleSeeder::class);

    expect(Role::query()->count())->toBe(4)
        ->and(Permission::query()->count())->toBe(count(RoleSeeder::PERMISSIONS))
        ->and(Role::findByName('publisher')->permissions)->toHaveCount(2);
});
