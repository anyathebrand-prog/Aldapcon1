<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/**
 * Role permissions, BY DIRECT URL — FR-9.7, AC-F9, TRD §8 Tier 1.
 *
 * The plan is explicit about how this must be tested: "test by direct URL,
 * never by clicking through the UI". A hidden menu item is not authorisation
 * (plan §2 rule 6), and a test that navigates by clicking can only ever prove
 * the menu is hidden.
 *
 * ── Scope note ───────────────────────────────────────────────────────────
 *
 * Phase 4 asserted this matrix against placeholder admin routes. Phase 5
 * replaced those with the Filament panel, so the panel-level checks now live
 * in tests/Feature/Content/AdminPanelAccessTest.php against the real
 * resources.
 *
 * What remains here is what Phase 5 did not move: the permission GRANTS
 * themselves, and the routes outside the panel. The member, payment and
 * verification screens arrive in Phase 13 and their route-level assertions
 * belong with them — asserting against routes that do not exist yet would
 * pass for the wrong reason.
 * ─────────────────────────────────────────────────────────────────────────
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
});

function roledUser(string $role): User
{
    $user = User::factory()->withTwoFactor()->create();
    $user->assignRole($role);

    return $user;
}

// ─────────────────────────────────────────────── unauthenticated

it('redirects an anonymous visitor away from the portal', function (): void {
    get('/portal')->assertRedirect('/login');
});

it('redirects an anonymous visitor away from the admin panel', function (): void {
    get('/admin/posts')->assertRedirect();
});

it('redirects an anonymous visitor away from two-factor enrolment', function (): void {
    get('/admin/two-factor-setup')->assertRedirect('/login');
});

// ─────────────────────────────────────────────── permission grants

it('never grants a publisher member, payment or certificate access', function (string $permission): void {
    // The single most important assertion in the authorisation model, and the
    // reason it holds at every route: the grant does not exist, so there is
    // nothing for a controller or a Filament resource to get wrong.
    //
    // AC-F9 singles out this case because it is the realistic internal
    // breach — a content editor trusted with the news section who should
    // never see a member's licence certificate.
    expect(roledUser('publisher')->can($permission))->toBeFalse();
})->with([
    'members.view', 'members.update', 'members.export', 'members.delete',
    'payments.view', 'payments.export', 'refunds.record',
    'applicants.view',
    'verifications.view', 'verifications.decide', 'documents.view',
    'data_requests.manage',
    'users.manage', 'audit.view', 'settings.manage',
]);

it('grants a publisher content and events and nothing else', function (): void {
    $publisher = roledUser('publisher');

    expect($publisher->can('content.manage'))->toBeTrue()
        ->and($publisher->can('events.manage'))->toBeTrue()
        ->and($publisher->getAllPermissions())->toHaveCount(2);
});

it('withholds the four super admin reserves from an admin', function (string $permission): void {
    expect(roledUser('admin')->can($permission))->toBeFalse();
})->with(['users.manage', 'audit.view', 'settings.manage', 'members.delete']);

it('grants an admin the verification permissions', function (string $permission): void {
    expect(roledUser('admin')->can($permission))->toBeTrue();
})->with(['verifications.view', 'verifications.decide', 'documents.view']);

it('grants a member no administrative permission at all', function (): void {
    // FR-9.7, audit IG-12. Portal access is by record ownership, not by
    // permission (Schema §5.1).
    $user = User::factory()->create();
    $user->assignRole('member');

    expect($user->getAllPermissions())->toBeEmpty();
});

// ─────────────────────────────────────────────── portal

it('lets a member into the portal', function (): void {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)->get('/portal')->assertOk();
});
