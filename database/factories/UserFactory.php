<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // Normalised to +234 on save (Schema §2.1).
            'phone' => '+2348'.$this->faker->numerify('#########'),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * Staff with a confirmed second factor. The pairing matters:
     * users_two_factor_pairing_check rejects a confirmation with no secret.
     */
    public function withTwoFactor(): self
    {
        return $this->state(fn (): array => [
            'two_factor_secret' => Str::random(32),
            'two_factor_recovery_codes' => json_encode([Str::random(10)]),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
