<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\Membership;
use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Membership> */
final class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'category_id' => MembershipCategory::factory(),
            'membership_number' => 'TST-2026-'.$this->faker->unique()->numerify('#####'),
            'status' => 'active',
            'joined_at' => now()->subMonths(2),
            // FR-6.1 — twelve months from activation.
            'expires_at' => now()->addMonths(10)->endOfDay(),
        ];
    }

    public function expiringSoon(): self
    {
        return $this->state(fn (): array => [
            'status' => 'expiring_soon',
            'expires_at' => now()->addDays(20)->endOfDay(),
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (): array => [
            'status' => 'expired',
            'expires_at' => now()->subDays(10)->endOfDay(),
        ]);
    }

    public function suspended(): self
    {
        return $this->state(fn (): array => ['status' => 'suspended']);
    }
}
