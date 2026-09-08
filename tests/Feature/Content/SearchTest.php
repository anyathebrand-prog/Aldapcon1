<?php

declare(strict_types=1);

use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;

use function Pest\Laravel\get;

/**
 * Site-wide search — FR-1.6, AC-F1.
 *
 * AC-F1 states the pair that matters: "Search for a term present in a
 * published post returns that post; search for a term in an unpublished draft
 * returns nothing."
 *
 * A search that returned a draft and then filtered it out of the display would
 * already have leaked the title, so every query filters at the database.
 */
it('finds a published post', function (): void {
    Post::factory()->published()->create([
        'title' => 'Enforcement action against a bank',
        'body' => 'The commission issued a penalty notice.',
    ]);

    get('/search?q=enforcement')
        ->assertOk()
        ->assertSee('Enforcement action against a bank');
});

it('never finds a draft', function (): void {
    Post::factory()->create([
        'title' => 'Unannounced merger discussions',
        'body' => 'Confidential.',
    ]);

    get('/search?q=merger')
        ->assertOk()
        ->assertDontSee('Unannounced merger discussions');
});

it('never finds a scheduled post before its time', function (): void {
    Post::factory()->scheduled()->create([
        'title' => 'Embargoed announcement',
        'body' => 'Not yet.',
    ]);

    get('/search?q=embargoed')
        ->assertOk()
        ->assertDontSee('Embargoed announcement');
});

it('finds a page', function (): void {
    Page::factory()->create([
        'title' => 'Membership categories',
        'slug' => 'membership-categories',
        'body' => 'Annual fees and eligibility.',
    ]);

    get('/search?q=eligibility')->assertOk()->assertSee('Membership categories');
});

it('finds a published question', function (): void {
    Faq::factory()->create(['question' => 'Can I pay by bank transfer?', 'answer' => 'Yes.']);

    get('/search?q=transfer')->assertOk()->assertSee('Can I pay by bank transfer?');
});

it('never finds an unpublished question', function (): void {
    Faq::factory()->unpublished()->create([
        'question' => 'An internal note about refunds',
        'answer' => 'Draft.',
    ]);

    get('/search?q=refunds')->assertOk()->assertDontSee('An internal note about refunds');
});

it('prompts rather than errors on an empty query', function (): void {
    // App Flow P-11 — "Empty query: prompt rather than error."
    get('/search')->assertOk()->assertSee('Enter a word or phrase');
});

it('offers a way onward when nothing matches', function (): void {
    // P-11 — "No results: suggest browsing news and events; do not present as
    // a failure."
    get('/search?q=zzzznothingmatchesthis')
        ->assertOk()
        ->assertSee('Browse news');
});

it('does not break on punctuation', function (): void {
    // plainto_tsquery is used rather than to_tsquery precisely so that an
    // apostrophe or a stray operator is treated as text. to_tsquery would
    // raise a syntax error and return a 500 to the visitor.
    Post::factory()->published()->create(['title' => 'A regular post', 'body' => 'Body.']);

    get('/search?q='.urlencode("what's the ndpc & why | does it matter?"))->assertOk();
});

it('truncates a very long query', function (): void {
    // P-11 — "Long query: truncate and sanitise."
    get('/search?q='.str_repeat('a', 500))->assertOk();
});
