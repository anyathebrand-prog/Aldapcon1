<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\ContentTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A post category or tag — Schema §2.6, FR-7.1, FR-7.3.
 */
final class ContentTerm extends Model
{
    /** @use HasFactory<ContentTermFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['type', 'name', 'slug'];

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_term', 'term_id', 'post_id');
    }
}
