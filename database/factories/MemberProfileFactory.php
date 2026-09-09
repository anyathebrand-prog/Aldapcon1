<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\MemberProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MemberProfile> */
final class MemberProfileFactory extends Factory
{
    protected $model = MemberProfile::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organisation' => $this->faker->company(),
            'job_title' => 'Data Protection Officer',
            'qualifications' => $this->faker->sentence(8),
            'ndpc_licence_number' => null,
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'referral_source' => 'A colleague',
        ];
    }
}
