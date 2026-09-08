<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Editable configuration — Schema §2.8, NFR 6.6, screen D-21.
 *
 * Cached in Redis from Phase 16 (TRD §7.1); read straight through until then.
 */
final class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['key', 'value', 'group', 'updated_by_user_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['value' => 'array', 'updated_at' => 'datetime'];
    }
}
