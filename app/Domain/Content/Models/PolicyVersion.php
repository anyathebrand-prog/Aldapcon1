<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\PolicyVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A versioned legal policy — Schema §2.8, FR-12.4.
 *
 * Never updated, never deleted. A new policy is a new row, so that a consent
 * record can always point at the exact text that was agreed to.
 */
final class PolicyVersion extends Model
{
    /** @use HasFactory<PolicyVersionFactory> */
    use HasFactory;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['slug', 'version', 'body', 'effective_from'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['effective_from' => 'datetime', 'created_at' => 'datetime'];
    }

    /**
     * The version in force for a policy right now.
     */
    public static function current(string $slug): ?self
    {
        return self::query()
            ->where('slug', $slug)
            ->where('effective_from', '<=', now())
            ->orderByDesc('effective_from')
            ->first();
    }
}
