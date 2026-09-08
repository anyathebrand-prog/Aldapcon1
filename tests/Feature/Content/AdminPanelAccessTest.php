<?php

declare(strict_types=1);

use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/**
 * Admin panel access — FR-9.7, AC-F9, plan C-6.
 *
 * Phase 4 proved the authorisation matrix against placeholder routes. Filament
 * now owns /admin, so the same guarantees are re-proved against the real
 * panel — otherwise the phase that introduced the admin surface would be the
 * phase that stopped testing it.
 *
 * By direct URL throughout. A hidden navigation item is not authorisation
 * (plan §2 rule 6).
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
});

function panelUser(string $role): User
{
    // withTwoFactor, or RequireTwoFactor redirects to enrolment and masks
    // what is being tested.
    $user = User::factory()->withTwoFactor()->create();
    $user->assignRole($role);

    return $user;
}

it('redirects an anonymous visitor to login', function (): void {
    get('/admin')->assertRedirect();
});

it('refuses a member the panel entirely', function (): void {
    // canAccessPanel — refused at the door, not a 403 inside it and not an
    // empty dashboard.
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)->get('/admin/posts')->assertForbidden();
});

it('lets a publisher manage content', function (string $path): void {
    // C-6 — events are content for this purpose; posts, pages, leadership and
    // FAQs are all covered by content.manage (Schema §2.2).
    actingAs(panelUser('publisher'))->get($path)->assertOk();
})->with([
    '/admin/posts',
    '/admin/pages',
    '/admin/leadership-profiles',
    '/admin/faqs',
]);

it('lets an admin manage content', function (string $path): void {
    actingAs(panelUser('admin'))->get($path)->assertOk();
})->with(['/admin/posts', '/admin/pages']);

it('forces staff without a second factor to enrolment', function (): void {
    // FR-4.3 carried into the panel: the middleware stack applies to Filament
    // exactly as it did to the placeholder routes.
    $user = User::factory()->create();
    $user->assignRole('admin');

    actingAs($user)->get('/admin/posts')->assertRedirect(route('two-factor.setup'));
});

it('never lets a system page be deleted', function (): void {
    // Six pages are linked by slug from navigation, the footer and the signup
    // flow. Deleting one does not remove a page; it breaks a hard-coded link,
    // and for the legal pages it breaks FR-12.4.
    $page = Page::factory()->system()->create();
    $ordinary = Page::factory()->create();

    $superAdmin = panelUser('super_admin');

    expect($superAdmin->can('delete', $page))->toBeFalse()
        ->and($superAdmin->can('delete', $ordinary))->toBeTrue();
});
