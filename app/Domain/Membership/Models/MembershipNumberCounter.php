<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The per-category number counter — Schema §2.3, FR-3.11.
 *
 * Read and written by AllocateMembershipNumber under a row lock, never
 * directly. Present as a model only so a category can create its own counter.
 */
final class MembershipNumberCounter extends Model
{
    protected $table = 'membership_number_counters';

    protected $primaryKey = 'category_id';

    public $incrementing = false;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['category_id', 'last_number'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['last_number' => 'integer'];
    }
}
