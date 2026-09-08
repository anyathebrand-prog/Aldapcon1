<?php

declare(strict_types=1);

use App\Domain\Content\Models\Announcement;
use App\Domain\Content\Models\Media;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\PolicyVersion;
use App\Domain\Identity\Models\ConsentRecord;
use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Database-level guarantees — Schema §2, plan Phase 3.
 *
 * Each of these is enforced by the database rather than by application code,
 * because a validation rule can be forgotten in one controller and a
 * constraint cannot.
 */
it('creates every Phase 3 table', function (string $table): void {
    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    'users', 'password_reset_tokens', 'login_attempts',
    'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
    'media', 'policy_versions', 'consent_records', 'settings', 'activity_log',
    'pages', 'leadership_profiles', 'faqs', 'announcements', 'slug_redirects',
]);

it('enables the postgres extensions the schema depends on', function (string $extension): void {
    $exists = DB::selectOne('SELECT 1 AS ok FROM pg_extension WHERE extname = ?', [$extension]);

    expect($exists)->not->toBeNull();
})->with(['citext', 'pgcrypto']);

it('treats email as case-insensitive', function (): void {
    // CITEXT. Without it, "Ada@Example.com" and "ada@example.com" become two
    // accounts and FR-3.10's duplicate check silently stops working.
    User::factory()->create(['email' => 'Ada@Example.com']);

    expect(User::query()->where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('rejects a duplicate email regardless of case', function (): void {
    User::factory()->create(['email' => 'ada@example.com']);

    expect(fn () => User::factory()->create(['email' => 'ADA@EXAMPLE.COM']))
        ->toThrow(QueryException::class);
});

it('rejects a confirmed second factor with no secret', function (): void {
    // users_two_factor_pairing_check. 2FA is mandatory for staff (FR-4.3), so
    // a confirmation with nothing behind it must not be representable.
    $user = User::factory()->create();

    expect(fn () => DB::table('users')->where('id', $user->id)->update([
        'two_factor_confirmed_at' => now(),
        'two_factor_secret' => null,
    ]))->toThrow(QueryException::class);
});

it('requires alt text on media', function (): void {
    // NFR 6.5 enforced in the schema, not merely as a validation rule, so no
    // code path can bypass it.
    $user = User::factory()->create();

    expect(fn () => DB::table('media')->insert([
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'path' => 'posts/x/original.jpg',
        'original_filename' => 'x.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1000,
        'alt_text' => null,
        'uploaded_by_user_id' => $user->id,
        'created_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('accepts media with alt text', function (): void {
    $media = Media::factory()->create();

    expect($media->alt_text)->not->toBeEmpty();
});

it('rejects a published announcement with no publication date', function (): void {
    // announcements_published_at_check — belt and braces, mirroring posts, so
    // a stuck job cannot leak an announcement with no date.
    $user = User::factory()->create();

    expect(fn () => DB::table('announcements')->insert([
        'title' => 'Leaked',
        'body' => 'Body',
        'status' => 'published',
        'published_at' => null,
        'created_by_user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('treats an announcement as visible only when published and dated', function (): void {
    $draft = Announcement::factory()->create();
    $live = Announcement::factory()->published()->create();
    $future = Announcement::factory()->create([
        'status' => 'published',
        'published_at' => now()->addWeek(),
    ]);

    expect($draft->isVisible())->toBeFalse()
        ->and($live->isVisible())->toBeTrue()
        ->and($future->isVisible())->toBeFalse();
});

it('rejects a second policy version with the same slug and version', function (): void {
    PolicyVersion::factory()->create(['slug' => 'privacy-policy', 'version' => '1.0']);

    expect(fn () => PolicyVersion::factory()->create(['slug' => 'privacy-policy', 'version' => '1.0']))
        ->toThrow(QueryException::class);
});

it('returns the policy version in force', function (): void {
    PolicyVersion::factory()->create([
        'slug' => 'privacy-policy', 'version' => '1.0', 'effective_from' => now()->subYear(),
    ]);
    $current = PolicyVersion::factory()->create([
        'slug' => 'privacy-policy', 'version' => '2.0', 'effective_from' => now()->subDay(),
    ]);
    PolicyVersion::factory()->create([
        'slug' => 'privacy-policy', 'version' => '3.0', 'effective_from' => now()->addMonth(),
    ]);

    // A future version must not be returned, and the previous one must remain
    // retrievable — a consent record pointing at a version nobody can read is
    // not evidence of anything (App Flow P-12).
    expect(PolicyVersion::current('privacy-policy')?->id)->toBe($current->id)
        ->and(PolicyVersion::query()->where('version', '1.0')->exists())->toBeTrue();
});

it('reads consent as the latest row per email and purpose', function (): void {
    // Withdrawal is a new row, never an update (Schema §2.8).
    $email = 'ada@example.com';

    ConsentRecord::factory()->create([
        'email' => $email, 'purpose' => 'marketing', 'granted' => true,
        'created_at' => now()->subDay(),
    ]);

    expect(ConsentRecord::currentlyGranted($email, 'marketing'))->toBeTrue();

    ConsentRecord::factory()->withdrawn()->create([
        'email' => $email, 'purpose' => 'marketing', 'created_at' => now(),
    ]);

    expect(ConsentRecord::currentlyGranted($email, 'marketing'))->toBeFalse()
        // Both rows survive. The evidence is the trail, not the latest value.
        ->and(ConsentRecord::query()->where('email', $email)->count())->toBe(2);
});

it('defines each enum with exactly the approved values', function (string $type, array $expected): void {
    // The CREATE TYPE guard skips creation when the type already exists, which
    // is what makes migrate:fresh work. The cost is that changing a value in
    // the migration would leave a stale type in place unnoticed — so the
    // labels are asserted rather than assumed.
    //
    // PostgreSQL cannot drop an enum value, so a mismatch here is never a
    // "just edit it" fix: it needs ALTER TYPE ... ADD VALUE and a decision.
    $labels = DB::table('pg_enum')
        ->join('pg_type', 'pg_type.oid', '=', 'pg_enum.enumtypid')
        ->where('pg_type.typname', $type)
        ->orderBy('pg_enum.enumsortorder')
        ->pluck('pg_enum.enumlabel')
        ->all();

    expect($labels)->toBe($expected);
})->with([
    ['content_status', ['draft', 'scheduled', 'published']],
    ['consent_purpose', ['membership_processing', 'marketing', 'event_processing']],
]);

it('rejects an unknown consent purpose', function (): void {
    // The enum is the guard. A typo in a controller becomes an error, not a
    // silently unrecorded consent.
    expect(fn () => DB::table('consent_records')->insert([
        'email' => 'ada@example.com',
        'purpose' => 'not_a_real_purpose',
        'granted' => true,
        'consent_text_snapshot' => 'x',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('marks system pages as undeletable', function (): void {
    $system = Page::factory()->system()->create();
    $ordinary = Page::factory()->create();

    expect($system->isDeletable())->toBeFalse()
        ->and($ordinary->isDeletable())->toBeTrue();
});

it('does not let is_system be set by mass assignment', function (): void {
    // A page becomes a system page by seeder or migration, never by a form.
    $page = Page::create([
        'title' => 'Ordinary', 'slug' => 'ordinary', 'body' => 'x', 'is_system' => true,
    ]);

    expect($page->fresh()?->is_system)->toBeFalse();
});

it('populates the search vector for full-text search', function (): void {
    // FR-1.6 reads these columns directly through PostgreSQL full-text.
    // No Scout (audit IG-2): its database driver performs LIKE matching
    // against model attributes and would never read a tsvector.
    Page::factory()->create(['title' => 'Membership categories', 'body' => 'Annual fees explained.']);

    $found = DB::selectOne(
        "SELECT id FROM pages WHERE search_vector @@ plainto_tsquery('english', ?)",
        ['membership']
    );

    expect($found)->not->toBeNull();
});
