<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\Announcement;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Announcement> */
final class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraphs(2, true),
            'status' => 'draft',
            'published_at' => null,
            'created_by_user_id' => User::factory(),
        ];
    }

    public function published(): self
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
