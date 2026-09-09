<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MembershipCategory>
 *
 * Fees here are PLACEHOLDERS. PRD Q1 (blocker B-3) is unanswered, so no real
 * category or fee can be seeded and none is invented (Schema §8.2).
 */
final class MembershipCategoryFactory extends Factory
{
    protected $model = MembershipCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = 'Category '.Str::upper($this->faker->unique()->bothify('??##'));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'applicant_type' => 'individual',
            'eligibility' => $this->faker->sentence(12),
            'benefits' => [$this->faker->sentence(6), $this->faker->sentence(6)],
            // Kobo. 2500000 is ₦25,000.
            'annual_fee_kobo' => 2_500_000,
            'currency' => 'NGN',
            // Individual DPO categories keep the fast path (FR-3.7).
            'requires_verification' => false,
            'is_active' => true,
            'number_prefix' => Str::upper($this->faker->unique()->lexify('???')),
            'sort_order' => 0,
        ];
    }

    /**
     * A licensed DPCO category: licence number, certificate upload and an
     * administrator's approval before any membership exists.
     */
    public function verifying(): self
    {
        return $this->state(fn (): array => [
            'applicant_type' => 'organisation',
            'requires_verification' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
