<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\ContentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContentTerm> */
final class ContentTermFactory extends Factory
{
    protected $model = ContentTerm::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'type' => 'category',
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }

    public function tag(): self
    {
        return $this->state(fn (): array => ['type' => 'tag']);
    }
}
