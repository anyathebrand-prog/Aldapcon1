<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A publicly served content image — Schema §2.6, NFR 6.5.
 *
 * alt_text is NOT NULL at the database level, so no code path can bypass it.
 *
 * NDPC certificates are deliberately NOT stored here (Schema §2.3): they need
 * a private disk, integrity hashing and their own retention rules, and
 * alt_text is meaningless for a licence certificate.
 */
final class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'uuid', 'disk', 'path', 'original_filename', 'mime_type',
        'size_bytes', 'width', 'height', 'alt_text', 'uploaded_by_user_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }
}
