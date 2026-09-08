<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Content\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Default settings — Schema §8.2, screen D-21.
 *
 * Values are placeholders where the association has not supplied real ones.
 * Reminder timings come straight from FR-6.3 and should not be changed
 * without changing that requirement.
 */
final class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Association details — placeholders pending the real ones (PRD A7).
            ['key' => 'association.name', 'group' => 'association', 'value' => 'ALDAPCON'],
            ['key' => 'association.email', 'group' => 'association', 'value' => 'info@aldapcon.org.ng'],
            ['key' => 'association.phone', 'group' => 'association', 'value' => ''],
            ['key' => 'association.address', 'group' => 'association', 'value' => ''],

            // FR-6.3 — reminders at 30, 7 and 1 days before expiry, and once
            // after. Each fires exactly once per cycle (Schema §2.3).
            ['key' => 'renewal.reminder_days_before', 'group' => 'membership', 'value' => [30, 7, 1]],
            ['key' => 'renewal.reminder_days_after', 'group' => 'membership', 'value' => [1]],

            // FR-6.2 — Active becomes Expiring soon within 30 days of expiry.
            ['key' => 'membership.expiring_soon_days', 'group' => 'membership', 'value' => 30],

            // Plan C-3 — the registration link lifetime. FR-3.8 says an
            // applicant may return "at any time", but an unexpiring signed
            // link that creates an account with a password is a security
            // weakness (App Flow G-3). 90 days is the proposed figure and
            // still needs sign-off.
            ['key' => 'signup.registration_token_days', 'group' => 'membership', 'value' => 90],

            // Mail sender — Phase 7 replaces these once B-5 is answered.
            ['key' => 'mail.from_address', 'group' => 'mail', 'value' => 'no-reply@aldapcon.org.ng'],
            ['key' => 'mail.from_name', 'group' => 'mail', 'value' => 'ALDAPCON'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['group' => $setting['group'], 'value' => $setting['value']]
            );
        }
    }
}
