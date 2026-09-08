<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\LeadershipProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
