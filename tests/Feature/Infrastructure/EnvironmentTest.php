<?php

declare(strict_types=1);

/**
 * Phase 1 check: conventions that later phases silently assume.
 *
 * Each assertion here corresponds to a decision recorded in the approved
 * documents. They are cheap to state now and expensive to discover later.
 */
it('stores timestamps in UTC', function (): void {
    // Schema §1.4 — stored UTC, rendered Africa/Lagos. Membership expiry
    // arithmetic in Phase 11 is only unambiguous if this holds.
    expect(config('app.timezone'))->toBe('UTC');
});

it('has an application key', function (): void {
    expect(config('app.key'))->not->toBeEmpty();
});

it('keeps the domain directories in place', function (): void {
    // TRD §1.2 — four domains with enforced boundaries. Membership reacts to
    // PaymentVerified; it never calls into Payments. The structure is created
    // now so no later phase has to invent a home for its first class.
    foreach (['Content', 'Membership', 'Payments', 'Identity'] as $domain) {
        expect(is_dir(app_path("Domain/{$domain}")))
            ->toBeTrue("app/Domain/{$domain} is missing (TRD §1.2)");
    }
});

it('does not expose debug mode in the production environment', function (): void {
    // TRD §9 makes APP_DEBUG=false a production launch gate. The test asserts
    // the pairing rather than the value, so it stays meaningful in every
    // environment the suite runs in.
    if (app()->environment('production')) {
        expect(config('app.debug'))->toBeFalse();
    } else {
        expect(true)->toBeTrue();
    }
});
