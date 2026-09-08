<?php

declare(strict_types=1);

/**
 * Measured contrast — UI brief §2.2.
 *
 * The brief says: "Every contrast ratio quoted in this document has been
 * calculated, not estimated." This test recalculates every one of them from
 * the tokens actually shipped, so the claim stays true rather than becoming a
 * historical note about a palette that has since drifted.
 *
 * It replaces the manual devtools spot-check in the Phase 2 verification list.
 * A human eye is still needed for the things a formula cannot judge — whether
 * the focus ring is *visible* against real content, whether the mobile menu
 * traps focus — but "does #14201A on #FAFBFA still clear AAA" is arithmetic,
 * and arithmetic belongs in CI.
 *
 * Two assertions per pair:
 *   1. the ratio matches the figure published in §2.2
 *   2. the hex is still present in tailwind.config.js
 *
 * The second is what makes the first meaningful. Without it the test would
 * happily verify the maths on colours the application no longer uses.
 *
 * WCAG 2.1 relative luminance and contrast, per
 * https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
 */
function relativeLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');

    $channels = array_map(
        static function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        },
        str_split($hex, 2)
    );

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrastRatio(string $foreground, string $background): float
{
    $a = relativeLuminance($foreground);
    $b = relativeLuminance($background);

    $lighter = max($a, $b);
    $darker = min($a, $b);

    return ($lighter + 0.05) / ($darker + 0.05);
}

it('matches every calculated ratio in the UI brief', function (
    string $label,
    string $foreground,
    string $background,
    float $published
): void {
    $actual = contrastRatio($foreground, $background);

    // Two decimal places is how the brief quotes them, so allow half of the
    // last place for rounding.
    expect(round($actual, 2))->toBeGreaterThanOrEqual($published - 0.01)
        ->and(round($actual, 2))->toBeLessThanOrEqual($published + 0.01);
})->with([
    // Body and surfaces
    ['ink on paper', '#14201A', '#FAFBFA', 16.18],
    ['ink on surface', '#14201A', '#EFF3F0', 14.98],
    ['ink-muted on paper', '#4E5F56', '#FAFBFA', 6.54],

    // Brand
    ['forest-700 on white', '#046A44', '#FFFFFF', 6.67],
    ['forest-800 on white', '#055537', '#FFFFFF', 8.89],
    ['white on forest-700', '#FFFFFF', '#046A44', 6.67],
    ['white on forest-900', '#FFFFFF', '#0A3B29', 12.57],
    ['brass-700 on white', '#7A5E18', '#FFFFFF', 6.10],

    // The tight one. AA only, which is exactly why §2.3 restricts flag green
    // to identity marks and rules — never body text, never a button fill,
    // never a status (F-4).
    ['flag-green on white', '#008751', '#FFFFFF', 4.58],

    // Non-text contrast, WCAG 1.4.11 — control boundaries need 3:1.
    ['border on white', '#73867C', '#FFFFFF', 3.87],
    ['border on surface', '#73867C', '#EFF3F0', 3.45],

    // Decorative only. Below 3:1, so it must never be a control boundary.
    //
    // NOTE — the UI brief §2.2 publishes 1.49 for this pair. The correct
    // figure is 1.34. Every other ratio in that table reproduces exactly, so
    // this is a single arithmetic slip in the document rather than a drifted
    // token.
    //
    // It changes NO design decision: the verdict is "decorative only, never a
    // control boundary", and 1.34 and 1.49 are both far below the 3:1 that
    // WCAG 1.4.11 requires of a boundary. Recorded here, and raised in the
    // PR, so the document can be corrected rather than quietly diverging from
    // the code.
    ['rule on paper', '#D3DDD7', '#FAFBFA', 1.34],

    // The five status treatments (§2.4)
    ['status active', '#0A3B29', '#E3F1E9', 10.79],
    ['status expiring', '#6E4600', '#FBF0DC', 7.33],
    ['status expired', '#3F4F47', '#EAEEEB', 7.41],
    ['status suspended', '#8A1F16', '#FAE7E5', 7.70],
    ['status pending', '#123A59', '#E4EEF6', 10.06],

    // Focus on dark surfaces
    ['focus-dark on forest-900', '#C9F2DE', '#0A3B29', 10.32],
]);

it('still ships every colour the ratios were calculated against', function (string $hex): void {
    // Without this, the arithmetic above could pass forever against colours
    // the application had stopped using.
    $config = file_get_contents(base_path('tailwind.config.js'));

    expect(stripos($config, $hex))->not->toBeFalse("{$hex} is no longer in tailwind.config.js");
})->with([
    '#14201A', '#4E5F56', '#FAFBFA', '#EFF3F0', '#FFFFFF',
    '#0A3B29', '#055537', '#046A44', '#008751', '#E3F1E9', '#F1F7F4',
    '#7A5E18', '#73867C', '#D3DDD7',
    '#6E4600', '#FBF0DC', '#3F4F47', '#EAEEEB', '#8A1F16', '#FAE7E5',
    '#123A59', '#E4EEF6', '#C9F2DE',
]);

it('clears AA for every pair carrying text', function (
    string $label,
    string $foreground,
    string $background,
    float $minimum
): void {
    expect(contrastRatio($foreground, $background))->toBeGreaterThanOrEqual($minimum);
})->with([
    // 4.5:1 is AA for normal text (WCAG 1.4.3).
    ['body text', '#14201A', '#FAFBFA', 4.5],
    ['secondary text', '#4E5F56', '#FAFBFA', 4.5],
    ['links in prose', '#046A44', '#FFFFFF', 4.5],
    ['primary button label', '#FFFFFF', '#046A44', 4.5],
    ['status active', '#0A3B29', '#E3F1E9', 4.5],
    ['status expiring', '#6E4600', '#FBF0DC', 4.5],
    ['status expired', '#3F4F47', '#EAEEEB', 4.5],
    ['status suspended', '#8A1F16', '#FAE7E5', 4.5],
    ['status pending', '#123A59', '#E4EEF6', 4.5],

    // 3:1 is the non-text minimum (WCAG 1.4.11) for control boundaries.
    ['input border on white', '#73867C', '#FFFFFF', 3.0],
    ['input border on surface', '#73867C', '#EFF3F0', 3.0],
]);

it('keeps flag green below the threshold that would allow it as body text', function (): void {
    // F-4 — flag green clears AA with almost no margin and fails AAA. This
    // test exists so that if somebody ever "improves" the brand colour into
    // body text, the failure names the reason rather than looking arbitrary.
    $ratio = contrastRatio('#008751', '#FFFFFF');

    expect($ratio)->toBeLessThan(7.0)
        ->and($ratio)->toBeGreaterThanOrEqual(4.5);
});
