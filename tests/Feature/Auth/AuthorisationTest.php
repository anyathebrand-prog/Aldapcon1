<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/**
 * Every role against every protected route, BY DIRECT URL — FR-9.7, AC-F9,
 * TRD §8 Tier 1.
 *
 * The plan is explicit about how this must be tested: "test by direct URL,
 * never by clicking through the UI". A menu item that is hidden is not
 * authorisation (plan §2 rule 6), and a test that navigates by clicking can
 * only ever prove the menu is hidden.
 *
 * AC-F9: "Role permissions hold under direct URL access, not only through
 * hidden menu items."
 *
 * The Publisher rows are the ones that matter most. AC-F4 and AC-F9 both
 * single out a Publisher reaching for member and payment data, because that
 * is the realistic internal breach: a content editor who is trusted with the
 * news section and should never see a member's licence certificate.
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
});

/**
 * @return array<string, string>
 */
function adminRoutes(): array
{
    return [
        'dashboard' => '/admin',
        'members' => '/admin/members',
        'payments' => '/admin/payments',
        'verifications' => '/admin/verifications',
        'audit log' => '/admin/audit-log',
        'users' => '/admin/users',
        'settings' => '/admin/settings',
        'news' => '/admin/news',
        'events' => '/admin/events',
    ];
}

function staffUser(string $role): User
{
    // withTwoFactor, because RequireTwoFactor would otherwise redirect every
    // staff request to enrolment and mask what is actually being tested.
    $user = User::factory()->withTwoFactor()->create();
    $user->assignRole($role);

    return $user;
}

// ─────────────────────────────────────────────── unauthenticated

it('redirects an anonymous visitor away from every admin route', function (string $path): void {
    get($path)->assertRedirect('/login');
})->with(adminRoutes());

it('redirects an anonymous visitor away from the portal', function (): void {
    get('/portal')->assertRedirect('/login');
});

// ─────────────────────────────────────────────── publisher

it('refuses a publisher every member, payment and certificate route', function (string $path): void {
    // The single most important assertion in this phase.
    actingAs(staffUser('publisher'))->get($path)->assertForbidden();
})->with([
    '/admin',
    '/admin/members',
    '/admin/payments',
    '/admin/verifications',
    '/admin/audit-log',
    '/admin/users',
    '/admin/settings',
]);

it('allows a publisher their own content and events routes', function (string $path): void {
    // C-6 — events are content for this purpose. Confirmed before Phase 3
    // seeded the grants.
    actingAs(staffUser('publisher'))->get($path)->assertOk();
})->with(['/admin/news', '/admin/events']);

// ─────────────────────────────────────────────── member

it('refuses a member every admin route', function (string $path): void {
    // FR-9.7 — the member role holds no administrative permission at all.
    // Portal access is by ownership, not permission.
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)->get($path)->assertForbidden();
})->with(adminRoutes());

it('allows a member into the portal', function (): void {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)->get('/portal')->assertOk();
});

// ─────────────────────────────────────────────── admin

it('allows an admin the operational routes', function (string $path): void {
    actingAs(staffUser('admin'))->get($path)->assertOk();
})->with([
    '/admin',
    '/admin/members',
    '/admin/payments',
    '/admin/verifications',
    '/admin/news',
    '/admin/events',
]);

it('refuses an admin the four super admin reserves', function (string $path): void {
    // members.delete, users.manage, audit.view and settings.manage are
    // Super Admin only (Schema §2.2). audit.view in particular: "a log an
    // actor can read is a log they can plan around" (§5.3 rule 3).
    actingAs(staffUser('admin'))->get($path)->assertForbidden();
})->with(['/admin/audit-log', '/admin/users', '/admin/settings']);

// ─────────────────────────────────────────────── super admin

it('allows a super admin everything', function (string $path): void {
    actingAs(staffUser('super_admin'))->get($path)->assertOk();
})->with(adminRoutes());
