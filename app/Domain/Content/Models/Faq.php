<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A frequently asked question — Schema §2.6, FR-1.1.
 *
 * App Flow P-09: the FAQ page stays unlinked from navigation until at least
 * one published question exists.
 */
final class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['question', 'answer', 'group', 'sort_order', 'is_published'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }
}
