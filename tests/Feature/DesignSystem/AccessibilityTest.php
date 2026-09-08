<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

use function Pest\Laravel\get;

/**
 * Accessibility regressions — UI brief §10, PRD NFR 6.5.
 *
 * Automated tooling catches roughly a third of real WCAG issues, which is why
 * the brief also requires a manual keyboard and screen-reader pass. These
 * tests cover the third that IS machine-checkable, so a failure found once by
 * axe cannot quietly return in a later refactor.
 *
 * Every assertion here corresponds to something that was, or could plausibly
 * become, a real violation. None is a proxy for a manual check.
 */

/**
 * Extract every form control that needs an accessible name, and confirm each
 * has a <label for> pointing at it.
 *
 * @return array{controls: list<string>, labels: list<string>}
 */
function labelledControls(string $html): array
{
    preg_match_all('/<(?:input|select|textarea)\b[^>]*\bid="([^"]+)"/', $html, $controls);
    preg_match_all('/<label\b[^>]*\bfor="([^"]+)"/', $html, $labels);

    return ['controls' => $controls[1], 'labels' => $labels[1]];
}

it('gives the file upload control a real label', function (): void {
    // Found by axe DevTools during the Phase 2 visual pass. The label was a
    // styled <span>, and because the input itself is sr-only the control had
    // no accessible name at all.
    $html = Blade::render('<x-file-upload name="cert" label="NDPC licence certificate" />');

    ['controls' => $controls, 'labels' => $labels] = labelledControls($html);

    expect($controls)->not->toBeEmpty()
        ->and($labels)->toContain('cert')
        ->and(array_diff($controls, $labels))->toBeEmpty();
});

it('labels every form control in every component', function (string $markup): void {
    // A single sweep, so a new component cannot ship an unlabelled control.
    $html = Blade::render($markup);

    ['controls' => $controls, 'labels' => $labels] = labelledControls($html);

    $unlabelled = array_diff($controls, $labels);

    expect($unlabelled)->toBeEmpty(
        'Unlabelled form control(s): '.implode(', ', $unlabelled)
    );
})->with([
    '<x-input name="email" label="Email address" />',
    '<x-input name="locked" label="Email" :readonly="true" readonlyReason="Ask an admin." />',
    '<x-select name="state" label="State" :options="[]" />',
    '<x-checkbox name="opt" label="Send me reminders" />',
    '<x-consent-checkbox name="c" purpose="Required.">I agree</x-consent-checkbox>',
    '<x-file-upload name="cert" label="Certificate" />',
]);

it('labels every form control on the gallery page', function (): void {
    // The gallery renders every component in every state, so this is the
    // broadest single check available without a browser.
    ['controls' => $controls, 'labels' => $labels] = labelledControls(get('/_gallery')->getContent());

    $unlabelled = array_diff($controls, $labels);

    expect($unlabelled)->toBeEmpty(
        'Unlabelled control(s) on the gallery: '.implode(', ', $unlabelled)
    );
});

it('gives every icon-only button an accessible name', function (): void {
    // The header's open and close controls are icons. Their <svg> is
    // aria-hidden, so the name has to come from sr-only text.
    $html = Blade::render('<x-site-header />');

    expect($html)->toContain('Open menu')
        ->and($html)->toContain('Close menu')
        ->and($html)->toContain('aria-hidden="true"');
});

it('marks decorative svg as hidden from assistive technology', function (string $markup): void {
    // Every icon in this system is decorative — the meaning is always carried
    // by adjacent text (§10, §11 never-7). An unhidden decorative svg is
    // announced as "graphic" and adds noise.
    $html = Blade::render($markup);

    preg_match_all('/<svg\b([^>]*)>/', $html, $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $attributes) {
        expect($attributes)->toContain('aria-hidden="true"');
    }
})->with([
    '<x-badge status="active" />',
    '<x-alert variant="error">Something failed</x-alert>',
    '<x-input name="e" label="Email" error="Invalid" />',
    '<x-input name="l" label="Email" :readonly="true" />',
    '<x-button :loading="true">Saving</x-button>',
]);

it('gives the page a language and one h1', function (): void {
    $html = get('/_gallery')->getContent();

    expect($html)->toContain('<html lang="en">')
        ->and(substr_count($html, '<h1'))->toBe(1);
});
