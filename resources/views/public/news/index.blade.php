{{-- P-05 News index — FR-7.3, App Flow P-05 --}}
<x-layouts::public title="News" description="News, announcements and analysis from ALDAPCON.">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">News</h1>

        @if ($categories->isNotEmpty())
            <nav class="mt-6 flex flex-wrap items-center gap-3" aria-label="Filter by category">
                <a href="/news"
                   @if ($activeCategory === null) aria-current="page" @endif
                   class="t-body-sm px-3 py-2 rounded-sm border {{ $activeCategory === null ? 'border-forest-700 bg-forest-100 text-forest-800 font-semibold' : 'border-border text-ink hover:bg-forest-50' }}">
                    All
                </a>
                @foreach ($categories as $category)
                    <a href="/news?category={{ $category->slug }}"
                       @if ($activeCategory === $category->slug) aria-current="page" @endif
                       class="t-body-sm px-3 py-2 rounded-sm border {{ $activeCategory === $category->slug ? 'border-forest-700 bg-forest-100 text-forest-800 font-semibold' : 'border-border text-ink hover:bg-forest-50' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        @if ($posts->isEmpty())
            {{-- App Flow P-05 — an honest single line, no fake placeholder
                 cards. A filtered index with no matches offers a way back. --}}
            @if ($activeCategory !== null)
                <x-empty-state heading="No posts in this category yet">
                    Nothing has been published under this category so far.
                    <x-slot:action>
                        <x-button href="/news" variant="secondary">Show all news</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state heading="No news yet">
                    The association has not published anything here yet. Check back soon.
                </x-empty-state>
            @endif
        @else
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                @foreach ($posts as $post)
                    <x-news-card :post="$post" />
                @endforeach
            </div>

            <div class="mt-9">{{ $posts->links() }}</div>
        @endif
    </div>
</x-layouts::public>
