<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\LeadershipProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An executive council member — Schema §2.6, FR-1.3.
 *
 * App Flow P-03: "For a compliance association this page does more persuading
 * than any other."
 */
final class LeadershipProfile extends Model
{
    /** @use HasFactory<LeadershipProfileFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name', 'position', 'bio', 'photo_media_id', 'sort_order', 'is_published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return BelongsTo<Media, $this> */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    /**
     * Up to two initials, for the placeholder shown when a profile has no
     * photograph (App Flow P-03 — never a broken image).
     *
     * Lives on the model rather than in the template so the leadership page
     * stays free of string manipulation, and so a single-word name cannot
     * produce an empty circle.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }
}
