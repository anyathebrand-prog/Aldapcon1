<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Content\Models\PolicyVersion;
use Illuminate\Database\Seeder;

/**
 * Policy versions — Schema §8.2. LAUNCH-BLOCKING.
 *
 * Consent cannot be recorded without a policy version to point at
 * (consent_records.policy_version_id), so at least one row per policy must
 * exist before the join flow can take a single applicant.
 *
 * The bodies here are PLACEHOLDERS. FR-12.4 requires the real Privacy Policy,
 * Cookie Policy and Terms of Use to be published and versioned before launch,
 * and they are association deliverables (TRD Q6), not something the platform
 * can write for them.
 *
 * Rows are never updated. A new policy is a new row.
 */
final class PolicyVersionSeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            'privacy-policy' => 'Privacy Policy',
            'cookie-policy' => 'Cookie Policy',
            'terms' => 'Terms of Use',
        ];

        foreach ($policies as $slug => $title) {
            PolicyVersion::firstOrCreate(
                ['slug' => $slug, 'version' => '0.1-draft'],
                [
                    'body' => "PLACEHOLDER — the approved {$title} has not been supplied. "
                        .'FR-12.4 requires this to be published and versioned before launch. '
                        .'Replace with a new row; never edit this one.',
                    'effective_from' => now(),
                ]
            );
        }
    }
}
