<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A news post — Schema §2.6, FR-7.1 to FR-7.4.
 *
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string $slug
 */
final class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'featured_media_id',
        'author_user_id', 'status', 'published_at',
        'meta_title', 'meta_description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /**
     * The only scope public queries may use — AC-F7.
     *
     * Both halves are load-bearing. Filtering on status alone would let a
     * scheduled post appear the moment somebody set the status; filtering on
     * the date alone would expose drafts with a past date. A draft URL must
     * 404, and a scheduled post must not appear a second early.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /** @return BelongsToMany<ContentTerm, $this> */
    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(ContentTerm::class, 'post_term', 'post_id', 'term_id');
    }
}
