<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Faq> */
final class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'question' => rtrim($this->faker->sentence(), '.').'?',
            'answer' => $this->faker->paragraph(),
            'group' => $this->faker->randomElement(['Membership', 'Events', 'Payments']),
            'sort_order' => $this->faker->numberBetween(0, 20),
            'is_published' => true,
        ];
    }

    public function unpublished(): self
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
