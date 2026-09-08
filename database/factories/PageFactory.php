<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Page> */
final class PageFactory extends Factory
{
    protected $model = Page::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => $this->faker->paragraphs(3, true),
            'is_system' => false,
        ];
    }

    public function system(): self
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }
}
