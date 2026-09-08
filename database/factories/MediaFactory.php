<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Content\Models\Media;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
final class MediaFactory extends Factory
{
    protected $model = Media::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $uuid = (string) Str::uuid();

        return [
            'uuid' => $uuid,
            'disk' => 'public',
            'path' => "posts/2026/09/{$uuid}/original.jpg",
            'original_filename' => 'photograph.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => $this->faker->numberBetween(20000, 900000),
            'width' => 1200,
            'height' => 675,
            // Always present. NFR 6.5 makes this NOT NULL at the database
            // level, so a factory omitting it would fail loudly — which is
            // the intent.
            'alt_text' => $this->faker->sentence(6),
            'uploaded_by_user_id' => User::factory(),
        ];
    }
}
