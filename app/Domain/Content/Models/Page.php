<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An editable page — Schema §2.6, FR-1.1, NFR 6.6.
 *
 * `is_system` marks the six pages the navigation and signup flow link to by
 * slug. They are editable but not deletable: deleting one breaks a hard-coded
 * link in the join flow.
 */
final class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title', 'slug', 'body', 'meta_title', 'meta_description', 'updated_by_user_id',
    ];

    /**
     * is_system is NOT fillable. A page becomes a system page by migration or
     * seeder, never by a form submission.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function isDeletable(): bool
    {
        return ! $this->is_system;
    }
}
