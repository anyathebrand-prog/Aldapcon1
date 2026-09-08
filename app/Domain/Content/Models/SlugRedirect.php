<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A 301 from a retired slug — Schema §2.6, App Flow P-06.
 *
 * Without this, changing a post's slug breaks every link the association has
 * ever shared.
 */
final class SlugRedirect extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['entity_type', 'old_slug', 'entity_id'];
}
