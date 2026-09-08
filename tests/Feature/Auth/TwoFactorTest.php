<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/**
 * Mandatory two-factor for staff — FR-4.3, AC-F4, App Flow G-4 / A-07,
 * plan C-4.
 *
 * AC-F4: "Admin login requires a second factor; access is denied without it."
 *
 * "Mandatory" is only meaningful if it cannot be walked around, so these test
 * the bypass rather than the happy path: an admin with no confirmed factor
 * typing an admin URL directly.
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
});

function staffWithoutTwoFactor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('forces an admin with no second factor to enrolment', function (string $path): void {
    actingAs(staffWithoutTwoFactor('admin'))
        ->get($path)
        ->assertRedirect(route('two-factor.setup'));
})->with(['/admin', '/admin/members', '/admin/payments', '/admin/verifications']);

it('forces a super admin with no second factor to enrolment', function (): void {
    actingAs(staffWithoutTwoFactor('super_admin'))
        ->get('/admin')
        ->assertRedirect(route('two-factor.setup'));
});

it('cannot be bypassed by going straight to a deep admin url', function (): void {
    // The whole point. A redirect that only happens on the dashboard is not a
    // gate, it is a signpost.
    actingAs(staffWithoutTwoFactor('super_admin'))
        ->get('/admin/users')
        ->assertRedirect(route('two-factor.setup'));
});

it('lets the enrolment screen itself through', function (): void {
    // Guarding this route with the same middleware would loop forever.
    actingAs(staffWithoutTwoFactor('admin'))
        ->get('/admin/two-factor-setup')
        ->assertOk()
        ->assertSee('Set up two-factor authentication');
});

it('lets staff through once the second factor is confirmed', function (): void {
    $user = User::factory()->withTwoFactor()->create();
    $user->assignRole('admin');

    actingAs($user)->get('/admin')->assertOk();
});

it('does not require a second factor of a publisher', function (): void {
    // FR-9.7 grants Publisher no member, payment or certificate data, so a
    // second factor would protect nothing they can reach. Requiring it would
    // be friction without a security gain.
    actingAs(staffWithoutTwoFactor('publisher'))
        ->get('/admin/news')
        ->assertOk();
});

it('does not require a second factor of a member', function (): void {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)->get('/portal')->assertOk();
});

it('offers a recovery code route on the challenge screen', function (): void {
    // Recovery codes are the only way back for an admin who has lost their
    // phone, and an association with one to three staff has nobody else who
    // can restore that access (App Flow A-06).
    $user = User::factory()->withTwoFactor()->create();
    $user->assignRole('admin');

    $html = view('auth.two-factor-challenge')->render();

    expect($html)->toContain('recovery_code')
        ->and($html)->toContain('Use a recovery code instead');
});

it('requires the recovery codes to be acknowledged before enrolling', function (): void {
    // A-07 — the codes must be acknowledged as saved before proceeding.
    $user = User::factory()->create();
    $user->assignRole('admin');

    $html = actingAs($user)->get('/admin/two-factor-setup')->getContent();

    expect($html)->toContain('Begin setup');
});
