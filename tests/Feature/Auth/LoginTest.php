<?php

declare(strict_types=1);

use App\Domain\Identity\Models\LoginAttempt;
use App\Domain\Identity\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

/**
 * Read the first validation error for a field from the session.
 *
 * TestResponse has no getSession(), and reaching for the session helper
 * directly keeps these assertions readable.
 */
function errorFor(string $field): string
{
    $errors = session('errors');

    return $errors === null ? '' : (string) $errors->first($field);
}

/**
 * Login, lockout and enumeration — FR-4.1, FR-4.4, FR-4.5, AC-F4.
 */
beforeEach(function (): void {
    seed(RoleSeeder::class);
    RateLimiter::clear('login');
});

it('shows the login screen', function (): void {
    get('/login')->assertOk()->assertSee('Log in');
});

it('logs a member in with correct credentials', function (): void {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $user->assignRole('member');

    post('/login', ['email' => 'ada@example.com', 'password' => 'password'])
        ->assertRedirect('/portal');

    expect(auth()->id())->toBe($user->id);
});

it('treats email as case-insensitive at login', function (): void {
    // CITEXT (Schema §2.1). Somebody typing Ada@Example.com on a phone
    // keyboard is the same person.
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $user->assignRole('member');

    post('/login', ['email' => 'ADA@EXAMPLE.COM', 'password' => 'password'])
        ->assertRedirect('/portal');
});

it('refuses a wrong password', function (): void {
    User::factory()->create(['email' => 'ada@example.com']);

    post('/login', ['email' => 'ada@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    assertGuest();
});

it('gives an identical message whether or not the email exists', function (): void {
    // AC-F4 — enumeration must be impossible through login. If "no such
    // account" and "wrong password" differ, the form becomes a way to test
    // which addresses are registered.
    User::factory()->create(['email' => 'ada@example.com']);

    post('/login', ['email' => 'ada@example.com', 'password' => 'wrong']);
    $known = errorFor('email');

    session()->forget('errors');

    post('/login', ['email' => 'nobody@example.com', 'password' => 'wrong']);
    $unknown = errorFor('email');

    expect($known)->not->toBeEmpty()
        ->and($known)->toBe($unknown);
});

it('refuses a deactivated account with the same generic message', function (): void {
    // A deactivated account must not be distinguishable either — otherwise
    // the login form reports who has been suspended.
    User::factory()->inactive()->create(['email' => 'ada@example.com']);

    post('/login', ['email' => 'ada@example.com', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    assertGuest();
});

it('records every attempt, successful or not', function (): void {
    // Schema §2.1 — the Redis limiter is fast but does not survive a cache
    // flush. This table is the durable record a security investigation reads.
    User::factory()->create(['email' => 'ada@example.com']);

    post('/login', ['email' => 'ada@example.com', 'password' => 'wrong']);
    post('/login', ['email' => 'ada@example.com', 'password' => 'password']);

    expect(LoginAttempt::query()->count())->toBe(2)
        ->and(LoginAttempt::query()->where('successful', false)->count())->toBe(1)
        ->and(LoginAttempt::query()->where('successful', true)->count())->toBe(1);
});

it('never records the password in a login attempt', function (): void {
    // TRD §6.4 — no secrets in logs.
    User::factory()->create(['email' => 'ada@example.com']);

    post('/login', ['email' => 'ada@example.com', 'password' => 'hunter2-is-secret']);

    $row = json_encode(LoginAttempt::query()->first()?->toArray());

    expect($row)->not->toContain('hunter2-is-secret');
});

it('locks out after six consecutive failures', function (): void {
    // AC-F4 names six. The seventh must be refused by the limiter rather than
    // reaching the credential check at all.
    User::factory()->create(['email' => 'ada@example.com']);

    for ($i = 0; $i < 6; $i++) {
        post('/login', ['email' => 'ada@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
    }

    post('/login', ['email' => 'ada@example.com', 'password' => 'wrong']);

    // The lockout message states a cooldown (App Flow A-08), which the
    // generic failure message does not.
    expect(errorFor('email'))->toContain('seconds');
});

it('lets a correct password through before the sixth failure', function (): void {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $user->assignRole('member');

    for ($i = 0; $i < 5; $i++) {
        post('/login', ['email' => 'ada@example.com', 'password' => 'wrong']);
    }

    post('/login', ['email' => 'ada@example.com', 'password' => 'password'])
        ->assertRedirect('/portal');
});

it('does not lock a second account out because of the first', function (): void {
    // Throttled by email AND IP together (TRD §2.3). Keyed on IP alone, one
    // person failing repeatedly would lock out everybody behind the same
    // office or carrier NAT.
    User::factory()->create(['email' => 'ada@example.com']);
    $other = User::factory()->create(['email' => 'chidi@example.com']);
    $other->assignRole('member');

    for ($i = 0; $i < 6; $i++) {
        post('/login', ['email' => 'ada@example.com', 'password' => 'wrong']);
    }

    post('/login', ['email' => 'chidi@example.com', 'password' => 'password'])
        ->assertRedirect('/portal');
});

it('logs out and ends the session', function (): void {
    // FR-4.4 — sessions expire on logout.
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user);
    post('/logout')->assertRedirect();

    assertGuest();
});

it('records the last login timestamp and address', function (): void {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $user->assignRole('member');

    post('/login', ['email' => 'ada@example.com', 'password' => 'password']);

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_ip)->not->toBeNull();
});
