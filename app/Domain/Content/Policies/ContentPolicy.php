<?php

declare(strict_types=1);

namespace App\Domain\Content\Policies;

use App\Domain\Identity\Models\User;

/**
 * Content authorisation — FR-9.7, AC-F9, Schema §5.2.
 *
 * One policy for every content model, because they share one permission:
 * `content.manage` covers posts, pages, leadership profiles and FAQs
 * (Schema §2.2).
 *
 * Registered per model in AppServiceProvider. Filament calls these for every
 * resource action, so the panel's navigation, buttons and direct URLs are all
 * governed by the same check — there is no separate "hide the menu item"
 * path that could drift from the real gate.
 *
 * Deletion is narrower than editing on pages: the six system pages are
 * editable but not deletable, because the navigation and signup flow link to
 * them by slug.
 */
final class ContentPolicy
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

    public function delete(User $user): bool
    {
        return $user->can('content.manage');
    }
}
