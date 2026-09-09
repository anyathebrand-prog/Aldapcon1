<?php

declare(strict_types=1);

namespace App\Domain\Membership\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\MembershipCategory;

/**
 * Category authorisation — FR-2.2, FR-9.7, Schema §2.2.
 *
 * `categories.manage` is held by Admin and Super Admin. A Publisher does not
 * have it: categories carry fees, and fees are money.
 */
final class MembershipCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function view(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('categories.manage');
    }

    /**
     * AC-F2 — "Deactivating a category removes it from the public join flow
     * but preserves existing members in it."
     *
     * Deleting a category that has members would orphan them. The database
     * refuses it anyway (ON DELETE RESTRICT), but refusing here means the
     * administrator gets an explanation rather than a constraint violation.
     */
    public function delete(User $user, MembershipCategory $category): bool
    {
        return $user->can('categories.manage')
            && $category->memberships()->doesntExist();
    }
}
