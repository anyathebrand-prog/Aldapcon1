<?php

declare(strict_types=1);

use App\Domain\Content\Models\ContentTerm;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LeadershipProfile;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\SlugRedirect;

use function Pest\Laravel\get;

/**
 * Public content pages — FR-1.1 to FR-1.6, FR-7.1 to FR-7.4, AC-F1, AC-F7.
 */

// ─────────────────────────────────────────────────────── home

it('shows the three latest published posts on the home page', function (): void {
    // AC-F1 — "Home page shows the three latest published posts, updating
    // automatically as content changes."
    Post::factory()->published()->count(5)->sequence(
        ['title' => 'Oldest', 'published_at' => now()->subDays(5)],
        ['title' => 'Fourth', 'published_at' => now()->subDays(4)],
        ['title' => 'Third', 'published_at' => now()->subDays(3)],
        ['title' => 'Second', 'published_at' => now()->subDays(2)],
        ['title' => 'Newest', 'published_at' => now()->subDay()],
    )->create();

    get('/')
        ->assertOk()
        ->assertSee('Newest')
        ->assertSee('Second')
        ->assertSee('Third')
        ->assertDontSee('Fourth')
        ->assertDontSee('Oldest');
});

it('hides the latest band entirely when nothing is published', function (): void {
    // App Flow P-01 — "the news block is hidden entirely, not shown with
    // placeholder text. An empty slot reads as neglect."
    Post::factory()->count(2)->create(); // drafts

    get('/')->assertOk()->assertDontSee('Latest');
});

// ─────────────────────────────────────────────────────── news

it('lists published posts on the news index', function (): void {
    Post::factory()->published()->create(['title' => 'A published post']);

    get('/news')->assertOk()->assertSee('A published post');
});

it('never lists a draft or scheduled post', function (): void {
    Post::factory()->create(['title' => 'Still a draft']);
    Post::factory()->scheduled()->create(['title' => 'Not yet due']);

    get('/news')
        ->assertOk()
        ->assertDontSee('Still a draft')
        ->assertDontSee('Not yet due');
});

it('404s a draft post by direct url', function (): void {
    // AC-F7 — "drafts are not publicly accessible by URL". Knowing the
    // address must not be enough.
    $post = Post::factory()->create(['slug' => 'secret-draft']);

    get('/news/'.$post->slug)->assertNotFound();
});

it('404s a scheduled post before its time', function (): void {
    $post = Post::factory()->scheduled()->create(['slug' => 'embargoed']);

    get('/news/'.$post->slug)->assertNotFound();
});

it('shows a scheduled post once its time has passed', function (): void {
    // AC-F7 — "a post scheduled for a future time appears without manual
    // action after it". The date does the work, not a job, so a stuck
    // scheduler cannot hold a post back.
    $post = Post::factory()->create([
        'title' => 'Now due',
        'slug' => 'now-due',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    get('/news/'.$post->slug)->assertOk()->assertSee('Now due');
});

it('filters the news index by category', function (): void {
    $category = ContentTerm::factory()->create(['name' => 'Enforcement', 'slug' => 'enforcement']);

    $tagged = Post::factory()->published()->create(['title' => 'Tagged post']);
    $tagged->terms()->attach($category);

    Post::factory()->published()->create(['title' => 'Untagged post']);

    get('/news?category=enforcement')
        ->assertOk()
        ->assertSee('Tagged post')
        ->assertDontSee('Untagged post');
});

it('301s an old slug to the current one', function (): void {
    // App Flow P-06 — "otherwise every shared link the association has ever
    // posted breaks".
    $post = Post::factory()->published()->create(['slug' => 'original-address']);

    $post->update(['slug' => 'new-address']);

    expect(SlugRedirect::query()->where('old_slug', 'original-address')->exists())->toBeTrue();

    get('/news/original-address')->assertRedirect('/news/new-address');
});

it('survives a slug changing and changing back', function (): void {
    // a → b → a. Without updateOrCreate the second rename violates the unique
    // index and an editor sees a database error for renaming twice.
    $post = Post::factory()->published()->create(['slug' => 'first']);

    $post->update(['slug' => 'second']);
    $post->update(['slug' => 'first']);

    get('/news/first')->assertOk();
});

// ─────────────────────────────────────────────────────── pages

it('serves an editable page by slug', function (): void {
    Page::factory()->create(['slug' => 'about', 'title' => 'About us', 'body' => 'Our history.']);

    get('/about')->assertOk()->assertSee('About us')->assertSee('Our history.');
});

it('404s an unknown page slug', function (): void {
    get('/no-such-page')->assertNotFound();
});

it('does not let the page catch-all swallow the portal', function (): void {
    // /{slug} is declared last and constrained, but the consequence of
    // getting that wrong is that /portal renders a 404 page instead of
    // redirecting to login — so it is asserted rather than assumed.
    get('/portal')->assertRedirect('/login');
});

// ─────────────────────────────────────────────────────── leadership

it('lists published leadership profiles', function (): void {
    LeadershipProfile::factory()->create(['name' => 'Ada Okonkwo', 'position' => 'President']);
    LeadershipProfile::factory()->unpublished()->create(['name' => 'Hidden Person']);

    get('/leadership')
        ->assertOk()
        ->assertSee('Ada Okonkwo')
        ->assertSee('President')
        ->assertDontSee('Hidden Person');
});

it('renders initials rather than a broken image when a profile has no photo', function (): void {
    // App Flow P-03 — "Missing photo: neutral initial-based placeholder,
    // never a broken image."
    LeadershipProfile::factory()->create(['name' => 'Ada Okonkwo', 'photo_media_id' => null]);

    get('/leadership')->assertOk()->assertSee('AO');
});

// ─────────────────────────────────────────────────────── faq

it('groups published questions on the faq page', function (): void {
    Faq::factory()->create([
        'question' => 'How much is membership?',
        'group' => 'Membership',
    ]);
    Faq::factory()->unpublished()->create(['question' => 'An unpublished question']);

    get('/faq')
        ->assertOk()
        ->assertSee('How much is membership?')
        ->assertSee('Membership')
        ->assertDontSee('An unpublished question');
});
