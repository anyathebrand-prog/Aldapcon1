<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PolicyVersion> */
final class PolicyVersionFactory extends Factory
{
    protected $model = PolicyVersion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => 'privacy-policy',
            'version' => $this->faker->unique()->numerify('#.#'),
            'body' => $this->faker->paragraphs(3, true),
            'effective_from' => now()->subMonth(),
        ];
    }
}
