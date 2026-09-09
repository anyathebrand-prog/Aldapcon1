<?php

declare(strict_types=1);

namespace App\Domain\Content\Data;

/**
 * One search hit — FR-1.6.
 *
 * The search query is a UNION across posts, pages and FAQs, so its rows are
 * anonymous objects with no type PHPStan can check. Mapping them into this
 * shape at the boundary gives the view something typed and stops the raw
 * database result travelling any further into the application.
 */
final readonly class SearchResult
{
    public function __construct(
        public string $type,
        public string $title,
        public string $url,
        public ?string $excerpt,
    ) {}

    /**
     * Human label for the result type, shown beside each hit.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'post' => 'News',
            'page' => 'Page',
            'faq' => 'Question',
            default => ucfirst($this->type),
        };
    }
}
