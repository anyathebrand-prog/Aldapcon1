<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\LeadershipProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeadershipProfile> */
final class LeadershipProfileFactory extends Factory
{
    protected $model = LeadershipProfile::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'position' => $this->faker->randomElement([
                'President', 'Vice President', 'Secretary General', 'Treasurer',
            ]),
            'bio' => $this->faker->paragraph(),
            // Null by default, so the no-photograph case is what tests see
            // first. P-03 must render a neutral placeholder, never a broken
            // image.
            'photo_media_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_published' => true,
        ];
    }

    public function unpublished(): self
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
