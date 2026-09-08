<?php

declare(strict_types=1);

/**
 * The 180 KB font budget — UI brief §3.1, F-10.
 *
 * The brief states a budget. A budget that is only stated is a claim; this
 * test makes it a control, which is the same distinction the schema draws
 * about the retention schedule (§6.4: "a retention schedule that exists only
 * in a document is not a control").
 *
 * F-10 is explicit that two families plus a width axis risks the 1.5 MB page
 * weight and 2.5s LCP budgets in PRD NFR 6.3, and that the fix is never to add
 * a third family. If this test fails, the order of retreat is fixed:
 *
 *   1. drop Literata italic (not currently shipped)
 *   2. reduce Archivo to two static weights
 *   3. never add a third family for the record panel
 */
const FONT_BUDGET_BYTES = 180 * 1024;

it('ships both variable font files', function (): void {
    expect(public_path('fonts/archivo-latin-var.woff2'))->toBeFile()
        ->and(public_path('fonts/literata-latin-var.woff2'))->toBeFile();
});

it('keeps the total font payload within the 180 KB budget', function (): void {
    $total = collect(glob(public_path('fonts/*.woff2')))
        ->sum(fn (string $path): int => filesize($path));

    expect($total)->toBeLessThanOrEqual(
        FONT_BUDGET_BYTES,
        sprintf(
            'Font payload is %d KB against a %d KB budget (UI brief F-10). '.
            'Drop Literata italic first, then reduce Archivo to two static '.
            'weights. Never add a third family.',
            (int) round($total / 1024),
            (int) round(FONT_BUDGET_BYTES / 1024)
        )
    );
});

it('ships real woff2 files rather than placeholders', function (): void {
    // A zero-byte or HTML file would pass a size budget trivially and fail
    // silently in the browser, so the signature is checked rather than assumed.
    foreach (glob(public_path('fonts/*.woff2')) as $path) {
        $signature = file_get_contents($path, false, null, 0, 4);

        expect($signature)->toBe('wOF2', basename($path).' is not a WOFF2 file');
    }
});
