<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\PolicyVersion;
use App\Domain\Identity\Models\ConsentRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConsentRecord> */
final class ConsentRecordFactory extends Factory
{
    protected $model = ConsentRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'purpose' => 'membership_processing',
            'granted' => true,
            'policy_version_id' => PolicyVersion::factory(),
            'consent_text_snapshot' => 'I agree to ALDAPCON processing my personal data as described in the Privacy Policy.',
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * Withdrawal is a new row, never an update (Schema §2.8).
     */
    public function withdrawn(): self
    {
        return $this->state(fn (): array => ['granted' => false]);
    }
}
