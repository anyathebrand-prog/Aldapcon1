<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Enum types — Schema §8.1.
 *
 * PERMANENT. PostgreSQL cannot drop a value from an enum. Every value written
 * here is written for the life of the database, which is why the plan requires
 * each one to be reviewed before the migration runs rather than after.
 *
 * Only the two enums Phase 3 tables actually use are created here. The
 * membership, payment and event enums belong to the phases that create their
 * tables — defining them early would lock in values before the requirement
 * that shapes them has been built, which is the mistake this comment exists to
 * prevent.
 *
 * content_status  — posts (Phase 5) and announcements (this phase).
 *                   `scheduled` exists because FR-7.2 grants posts draft and
 *                   scheduled publishing.
 *
 *                   NOTE: announcements share this type and have no stated
 *                   requirement for scheduling. That is the same shape audit
 *                   IG-4 corrected for events, where a shared enum left an
 *                   unreachable value that "invites somebody to implement it
 *                   later with no requirement behind it". The approved schema
 *                   nonetheless specifies content_status for announcements, so
 *                   it is followed here and the tension is recorded rather
 *                   than resolved unilaterally. See the Phase 3 PR.
 *
 * consent_purpose — FR-12.2. One row per purpose, never bundled.
 *                   Schema Q6 notes that a public member directory would add
 *                   a fourth purpose, and that adding it now would be cheaper
 *                   than later. It is not added, because PRD Q7 is unanswered
 *                   and an unused enum value is exactly what the note above
 *                   warns about.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $types = [
        'content_status' => ['draft', 'scheduled', 'published'],
        'consent_purpose' => ['membership_processing', 'marketing', 'event_processing'],
    ];

    public function up(): void
    {
        foreach ($this->types as $name => $values) {
            $quoted = implode(', ', array_map(
                fn (string $v): string => "'".$v."'",
                $values
            ));

            DB::statement("CREATE TYPE {$name} AS ENUM ({$quoted})");
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys($this->types)) as $name) {
            DB::statement("DROP TYPE IF EXISTS {$name}");
        }
    }
};
