<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->sentence(20),
            'body' => $this->faker->paragraphs(5, true),
            'author_user_id' => User::factory(),
            // Draft by default, so a test that wants a visible post has to say
            // so. The opposite default would make "drafts are not publicly
            // accessible" easy to assert by accident.
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * FR-7.2 — status published with a future date. The publish job flips
     * nothing; the date is what holds it back.
     */
    public function scheduled(): self
    {
        return $this->state(fn (): array => [
            'status' => 'scheduled',
            'published_at' => now()->addWeek(),
        ]);
    }
}
