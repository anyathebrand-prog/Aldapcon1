<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

/**
 * Component render tests — plan Phase 2, "component render tests for each
 * variant".
 *
 * These assert the rules the UI brief is emphatic about and that are easy to
 * lose in a later refactor. They are not snapshot tests: asserting on exact
 * markup would fail on every whitespace change and teach everyone to
 * regenerate rather than read.
 */

// ---------------------------------------------------------------- buttons

it('renders every button variant', function (string $variant, string $expectedClass): void {
    $html = Blade::render('<x-button variant="'.$variant.'">Continue</x-button>');

    expect($html)->toContain($expectedClass)
        ->and($html)->toContain('Continue');
})->with([
    ['primary', 'bg-forest-700'],
    ['secondary', 'border-border'],
    ['quiet', 'text-forest-700'],
    ['destructive', 'text-error-fg'],
]);

it('renders every button size', function (string $size, string $expected): void {
    expect(Blade::render('<x-button size="'.$size.'">Go</x-button>'))->toContain($expected);
})->with([
    ['sm', 'min-h-[36px]'],
    ['md', 'min-h-control'],
    ['lg', 'min-h-control-lg'],
]);

it('announces the loading state to screen readers', function (): void {
    // §6 — the button keeps its width and the live region carries the meaning
    // for anyone who cannot see the spinner.
    $html = Blade::render('<x-button :loading="true">Confirming</x-button>');

    expect($html)->toContain('aria-live="polite"')
        ->and($html)->toContain('Working')
        ->and($html)->toContain('disabled');
});

it('states what would enable a disabled button', function (): void {
    // §6 — a dead button with no explanation is a dead end. The reason is
    // rendered AND wired, so a screen reader reaches it too.
    $html = Blade::render(
        '<x-button :disabled="true" disabledReason="Select a category to continue.">Continue</x-button>'
    );

    expect($html)->toContain('Select a category to continue.')
        ->and($html)->toContain('aria-disabled="true"')
        ->and($html)->toContain('aria-describedby');
});

// ---------------------------------------------------------------- inputs

it('always renders a visible label, never a placeholder as label', function (): void {
    // §5.2, §11 never-6 — placeholder-as-label disappears on focus and fails
    // cognitive accessibility outright.
    $html = Blade::render('<x-input name="email" label="Email address" placeholder="name@example.com" />');

    expect($html)->toContain('<label')
        ->and($html)->toContain('Email address')
        ->and($html)->toContain('for="email"');
});

it('wires input errors to the field rather than signalling by colour', function (): void {
    // §10, §11 never-7 — border, icon and text, with aria-invalid and
    // aria-describedby. Never colour alone.
    $html = Blade::render('<x-input name="email" label="Email" error="Enter a valid email address" />');

    expect($html)->toContain('aria-invalid="true"')
        ->and($html)->toContain('aria-describedby="email-error"')
        ->and($html)->toContain('Enter a valid email address')
        ->and($html)->toContain('border-error-border');
});

it('marks locked fields readonly and never disabled', function (): void {
    // F-7 — disabled controls are skipped by some screen readers and typically
    // fail contrast. readonly keeps them focusable and readable.
    $html = Blade::render(
        '<x-input name="email" label="Email" value="a@b.com" :readonly="true" readonlyReason="An administrator can change this." />'
    );

    expect($html)->toContain('readonly')
        ->and($html)->not->toContain('disabled')
        ->and($html)->toContain('An administrator can change this.')
        ->and($html)->toContain('bg-surface');
});

// ---------------------------------------------------------------- consent

it('never pre-ticks a consent checkbox', function (): void {
    // FR-12.2 and F-6. There is deliberately no `checked` prop on this
    // component, so pre-ticking cannot be reached by passing an argument.
    $html = Blade::render(
        '<x-consent-checkbox name="c" purpose="Required.">I agree</x-consent-checkbox>'
    );

    expect($html)->not->toContain('checked')
        ->and($html)->toContain('type="checkbox"');
});

it('renders consent as a square checkbox and never a toggle', function (): void {
    // §11 never-5 — a toggle implies a default-on state.
    $html = Blade::render(
        '<x-consent-checkbox name="c" purpose="Required.">I agree</x-consent-checkbox>'
    );

    expect($html)->toContain('rounded-none')
        ->and($html)->not->toContain('role="switch"');
});

// ---------------------------------------------------------------- status

it('gives every status a shape as well as a colour', function (string $status, string $label): void {
    // §2.4, F-3 — deuteranopia makes active-green and expiring-amber
    // unreliable, so the shape difference is load-bearing, not decorative.
    $html = Blade::render('<x-badge status="'.$status.'" />');

    expect($html)->toContain($label)
        ->and($html)->toContain('<svg');
})->with([
    ['active', 'Active'],
    ['expiring', 'Expiring soon'],
    ['expired', 'Expired'],
    ['suspended', 'Suspended'],
    ['pending', 'In review'],
]);

it('does not style expired as an error', function (): void {
    // §2.4 — an expired member is the highest-value renewal prospect in the
    // product. Red says "you failed"; slate says "this needs renewing".
    $html = Blade::render('<x-badge status="expired" />');

    expect($html)->toContain('status-expired')
        ->and($html)->not->toContain('error');
});

it('distinguishes in review from expired', function (): void {
    // §11 never-20 — two neutral statuses meaning opposite things is a
    // legibility failure that colour-blind users would feel worst.
    $pending = Blade::render('<x-badge status="pending" />');
    $expired = Blade::render('<x-badge status="expired" />');

    expect($pending)->toContain('status-pending-bg')
        ->and($expired)->toContain('status-expired-bg')
        ->and($pending)->not->toContain('status-expired-bg');
});

// ---------------------------------------------------------------- alerts

it('renders every alert variant with an announcement role', function (string $variant, string $role): void {
    $html = Blade::render('<x-alert variant="'.$variant.'">Message</x-alert>');

    expect($html)->toContain('role="'.$role.'"')
        ->and($html)->toContain('Message');
})->with([
    ['success', 'status'],
    ['warning', 'status'],
    ['error', 'alert'],
    ['info', 'status'],
]);

// ---------------------------------------------------------------- upload

it('states upload limits above the control, not only in the error', function (): void {
    // §11 always-15 — discovering an 8 MB cap by breaching it, after paying,
    // is a bad moment in an already anxious flow.
    $html = Blade::render('<x-file-upload name="cert" label="Certificate" />');

    expect($html)->toContain('PDF, JPG or PNG')
        ->and($html)->toContain('Maximum 8 MB');
});

it('gives the upload control a real keyboard button', function (): void {
    // A drag-only zone is not operable (§5.2).
    $html = Blade::render('<x-file-upload name="cert" label="Certificate" />');

    expect($html)->toContain('Choose a file')
        ->and($html)->toContain('type="button"');
});

it('carries upload progress in text as well as a bar', function (): void {
    // F-15 — under prefers-reduced-motion the bar is hidden, so the percentage
    // and the live region must convey identical information.
    $html = Blade::render('<x-file-upload name="cert" label="Certificate" />');

    expect($html)->toContain('aria-live="polite"')
        ->and($html)->toContain('motion-reduce:hidden');
});

// ---------------------------------------------------------------- record

it('renders the membership record panel with square corners', function (): void {
    // §4.4, §1.3 — the record panel is square-cornered and nothing else is.
    // That is what makes it read as a document among interface elements.
    $html = Blade::render(
        '<x-record-panel number="DPCO-2026-00034" category="Licensed DPCO" status="active" validUntil="14 March 2027" />'
    );

    expect($html)->toContain('DPCO-2026-00034')
        ->and($html)->toContain('rounded-none')
        ->and($html)->toContain('t-record-number')
        ->and($html)->toContain('text-brass-700')
        ->and($html)->toContain('14 March 2027');
});

it('gives the record panel no hover or click affordance', function (): void {
    // §5.4 — it is a record, not a control.
    $html = Blade::render(
        '<x-record-panel number="X-1" category="C" status="active" validUntil="today" />'
    );

    expect($html)->not->toContain('hover:')
        ->and($html)->not->toContain('<button')
        ->and($html)->not->toContain('<a ');
});

// ---------------------------------------------------------------- empty

it('renders an empty state with a heading and an explanation', function (): void {
    // §11 always-9 — never a bare empty box.
    $html = Blade::render('<x-empty-state heading="No events yet">Nothing scheduled.</x-empty-state>');

    expect($html)->toContain('No events yet')
        ->and($html)->toContain('Nothing scheduled.');
});
