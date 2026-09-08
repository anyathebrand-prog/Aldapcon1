<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production seed — Schema §8.2.
 *
 * Everything here is required at install. Order matters: roles before any
 * user, policy versions before any consent can be recorded.
 *
 * Deliberately NOT here:
 *
 * - The Super Admin account. Schema §8.2 requires it to be created by a
 *   console command with a forced password change and 2FA enrolment on first
 *   login. A seeded admin with a known password is a backdoor that survives
 *   into production. Built in Phase 4 with the rest of authentication.
 *
 * - Membership categories. PRD Q1 (blocker B-3) is unanswered, so no real
 *   categories or fees can be seeded, and placeholder money on a public page
 *   is worse than none.
 *
 * Fake members, applicants, payments and events are STAGING ONLY (TRD §9) and
 * live in a separate seeder from Phase 6 onward. Production data is never
 * copied down, and fake data is never seeded up.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PolicyVersionSeeder::class,
            SettingsSeeder::class,
            SystemPageSeeder::class,
        ]);
    }
}
