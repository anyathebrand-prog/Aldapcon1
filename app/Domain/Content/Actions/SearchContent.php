<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Domain\Content\Data\SearchResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Site-wide search — FR-1.6, AC-F1.
 *
 * PostgreSQL full-text, queried directly. NO SCOUT (audit IG-2): Scout's
 * database driver performs LIKE matching against model attributes and does not
 * read a tsvector column, so pairing it with the generated search_vector
 * columns would leave one of the two unused.
 *
 * AC-F1 requires that a term in a published post is found and a term in an
 * unpublished draft is not. Every query below therefore filters on
 * publication state at the database, not after the fact — a search that
 * returns a draft and then hides it has already leaked the title.
 */
final class SearchContent
{
    /**
     * @return Collection<int, SearchResult>
     */
    public function __invoke(string $query): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return collect();
        }

        // plainto_tsquery, not to_tsquery: it takes what a person typed and
        // never throws on punctuation. to_tsquery would 500 on an apostrophe.
        $posts = DB::table('posts')
            ->selectRaw("
                'post' AS type,
                title,
                '/news/' || slug AS url,
                excerpt,
                ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank
            ", [$query])
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('deleted_at');

        $pages = DB::table('pages')
            ->selectRaw("
                'page' AS type,
                title,
                '/' || slug AS url,
                NULL AS excerpt,
                ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank
            ", [$query])
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query]);

        $faqs = DB::table('faqs')
            ->selectRaw("
                'faq' AS type,
                question AS title,
                '/faq' AS url,
                NULL AS excerpt,
                ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank
            ", [$query])
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query])
            ->where('is_published', true);

        $rows = $posts->unionAll($pages)->unionAll($faqs)
            ->orderByDesc('rank')
            ->limit(50)
            ->get();

        // Mapped at the boundary so the raw union — anonymous objects with no
        // checkable type — does not travel into the view.
        return $rows->map(fn (object $row): SearchResult => new SearchResult(
            type: (string) $row->type,
            title: (string) $row->title,
            url: (string) $row->url,
            excerpt: is_string($row->excerpt) ? $row->excerpt : null,
        ))->values();
    }
}
