<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\MemberDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An uploaded NDPC certificate — Schema §2.3, TRD §11.7.
 *
 * Lives on the private disk and is NEVER reachable by URL. It is streamed by
 * a controller after a policy check (FR-3.13.2, screen D-24). App Flow D-24 is
 * specific even about the failure: an unauthenticated request gets 404, not
 * 403, because a 403 confirms the document exists.
 */
final class MemberDocument extends Model
{
    /** @use HasFactory<MemberDocumentFactory> */
    use HasFactory;

    use SoftDeletes;

    public const UPDATED_AT = null;

    public const CREATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'uuid', 'applicant_id', 'user_id', 'type', 'disk', 'path',
        'original_filename', 'mime_type', 'size_bytes', 'sha256',
        'superseded_by_id', 'uploaded_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime', 'size_bytes' => 'integer'];
    }

    /** @return BelongsTo<Applicant, $this> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the stored bytes still match what was uploaded.
     *
     * Worth one column for a body whose entire product is verification: an
     * administrator can confirm the document they are reviewing is the
     * document that was submitted (Schema §2.3).
     */
    public function matchesHash(string $contents): bool
    {
        return hash_equals($this->sha256, hash('sha256', $contents));
    }
}
