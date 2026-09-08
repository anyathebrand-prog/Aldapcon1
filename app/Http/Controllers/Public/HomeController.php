<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Content\Models\Post;
use Illuminate\Contracts\View\View;

/**
 * P-01 Home — FR-1.2, App Flow P-01.
 *
 * "Establish credibility fast and route the visitor to Join."
 *
 * Exactly three latest posts and up to three upcoming events (FR-1.2). Events
 * arrive in Phase 12; the home page is written so that adding them is a query,
 * not a redesign.
 */
final class HomeController
{
    public function __invoke(): View
    {
        return view('public.home', [
            // AC-F1 — "shows the three latest published posts, updating
            // automatically as content changes".
            'posts' => Post::query()
                ->published()
                ->with('author')
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
