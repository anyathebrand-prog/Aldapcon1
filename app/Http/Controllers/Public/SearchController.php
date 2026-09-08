<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Content\Actions\SearchContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * P-11 search results — FR-1.6, AC-F1.
 */
final class SearchController
{
    public function __invoke(Request $request, SearchContent $search): View
    {
        // App Flow P-11 — "Long query: truncate and sanitise."
        $query = Str::limit(trim((string) $request->query('q', '')), 120, '');

        return view('public.search', [
            'query' => $query,
            // Empty query is a prompt, not an error (P-11).
            'results' => $query === '' ? collect() : $search($query),
        ]);
    }
}
