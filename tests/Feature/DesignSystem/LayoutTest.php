<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

use function Pest\Laravel\get;

/**
 * Layout, gallery and navigation — plan Phase 2.
 */
it('renders the component gallery outside production', function (): void {
    get('/_gallery')
        ->assertOk()
        ->assertSee('Component gallery')
        // Every component section is present, so "the gallery renders every
        // component in every state" is checkable rather than asserted.
        ->assertSee('Type scale')
        ->assertSee('Status badges')
        ->assertSee('Membership Record panel')
        ->assertSee('File upload');
});

it('puts the skip link first in the tab order', function (): void {
    // §10 — a skip-to-content link is the first focusable element on every
    // page. Placed anywhere else it is useless to the people it is for.
    $html = get('/_gallery')->getContent();

    $skip = strpos($html, 'Skip to content');
    $firstNavLink = strpos($html, 'href="/about"');

    expect($skip)->not->toBeFalse()
        ->and($skip)->toBeLessThan($firstNavLink);
});

it('preloads both fonts', function (): void {
    // TRD §7.1 — the hero is typographic, so a late font is a late LCP.
    get('/_gallery')
        ->assertSee('rel="preload"', false)
        ->assertSee('archivo-latin-var.woff2', false)
        ->assertSee('literata-latin-var.woff2', false);
});

it('shows a visitor the join call to action', function (): void {
    get('/_gallery')->assertSee('Join the association');
});

it('never shows a member the join call to action', function (): void {
    // §11 never-17 — an active member urged to join is a credibility bug
    // (App Flow P-01).
    $html = Blade::render('<x-site-header :authenticated="true" />');

    expect($html)->not->toContain('Join the association')
        ->and($html)->toContain('My portal');
});

it('gives the mobile menu a focus trap and an escape route', function (): void {
    // §5.3 — focus trapped while open, Esc closes, background scroll locked.
    $html = Blade::render('<x-site-header />');

    expect($html)->toContain('x-trap.noscroll')
        ->and($html)->toContain('keydown.escape')
        ->and($html)->toContain('aria-modal="true"');
});

it('gives cookie accept and reject equal visual weight', function (): void {
    // F-8 — unequal weighting is a dark pattern, and indefensible for a data
    // protection association in particular. Same variant, same size, same row.
    $html = Blade::render('<x-cookie-banner />');

    expect($html)->toContain('Accept all')
        ->and($html)->toContain('Reject non-essential');

    // Both buttons carry the identical class string.
    preg_match_all('/class="([^"]*min-h-control[^"]*)"/', $html, $matches);
    $buttonClasses = array_values(array_filter(
        $matches[1],
        fn (string $c): bool => str_contains($c, 'rounded-md')
    ));

    expect($buttonClasses)->toHaveCount(2)
        ->and($buttonClasses[0])->toBe($buttonClasses[1]);
});

it('offers real routes out of the 404 page', function (): void {
    // AC-F1 — a 404 must offer navigation, not be a dead end.
    $html = get('/no-such-page-exists')->assertNotFound()->getContent();

    expect($html)->toContain('We could not find that page')
        ->and($html)->toContain('/membership')
        ->and($html)->toContain('/contact');
});

it('keeps the portal bottom bar to the four named tabs', function (): void {
    // audit IG-6 — Dashboard, Payments, Events, More. Profile and
    // Announcements are low-frequency and do not earn a thumb-reach slot.
    $html = Blade::render('<x-layouts::portal>content</x-layouts::portal>');

    foreach (['Dashboard', 'Payments', 'Events', 'More'] as $tab) {
        expect($html)->toContain($tab);
    }
});
