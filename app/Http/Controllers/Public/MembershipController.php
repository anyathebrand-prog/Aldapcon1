<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Membership\Models\MembershipCategory;
use Illuminate\Contracts\View\View;

/**
 * P-04 membership overview and J-01 category selection — FR-1.4, FR-2.1.
 *
 * App Flow P-04: "Let a visitor identify their category and its price."
 *
 * The Join actions lead nowhere until Phase 9a builds the payment flow. That
 * is deliberate sequencing, not an oversight — the plan has this phase end
 * with "Join buttons are present but lead nowhere until Phase 9."
 */
final class MembershipController
{
    public function index(): View
    {
        return view('public.membership', [
            'categories' => $this->activeCategories(),
        ]);
    }

    public function join(): View
    {
        return view('public.join.categories', [
            'categories' => $this->activeCategories(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, MembershipCategory>
     */
    private function activeCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return MembershipCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
