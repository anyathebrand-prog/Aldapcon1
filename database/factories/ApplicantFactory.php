<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Membership\Models\Applicant;
use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Applicant> */
final class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'category_id' => MembershipCategory::factory(),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+2348'.$this->faker->numerify('#########'),
            // Created at J-02, before payment (FR-3.2).
            'status' => 'initiated',
            'fee_kobo_at_initiation' => 2_500_000,
        ];
    }

    /**
     * Paid but not yet registered — the D-04 queue (FR-3.8).
     */
    public function paid(): self
    {
        return $this->state(fn (): array => [
            'status' => 'paid',
            'paid_at' => now()->subDay(),
        ]);
    }
}
