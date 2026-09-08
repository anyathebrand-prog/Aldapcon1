<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A member-facing announcement — Schema §2.6, FR-5.5.
 *
 * The only table in the schema created from inference rather than a stated
 * requirement (App Flow G-6, plan C-2, confirmed before the migration was
 * written).
 *
 * Expired members lose read access entirely (FR-6.5). The portal explains
 * that rather than showing an empty list (App Flow M-06).
 *
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $published_at
 */
final class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = ['title', 'body', 'status', 'published_at', 'created_by_user_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /**
     * Belt and braces, mirroring posts (Schema §2.6): the status AND the date
     * are both checked, so a stuck job cannot leak an announcement early.
     */
    public function isVisible(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }
}
