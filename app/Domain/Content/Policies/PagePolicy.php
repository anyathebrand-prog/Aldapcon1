<?php

declare(strict_types=1);

namespace App\Domain\Content\Policies;

use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;

/**
 * Page authorisation — FR-9.7, Schema §2.6.
 *
 * Same permission as other content, with one difference that matters:
 * a SYSTEM page cannot be deleted by anybody, at any role.
 *
 * About, Contact, the FAQ and the three legal pages are linked by slug from
 * the navigation, the footer and the signup flow. Deleting one does not
 * remove a page; it breaks a hard-coded link and, for the legal pages, breaks
 * FR-12.4 — a consent record pointing at a policy nobody can read is not
 * evidence of anything.
 */
final class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function view(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('content.manage') && ! $page->is_system;
    }
}
