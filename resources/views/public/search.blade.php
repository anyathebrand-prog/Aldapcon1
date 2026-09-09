{{-- P-11 Search results — FR-1.6, AC-F1 --}}
<x-layouts::public title="Search">
    <div class="mx-auto max-w-prose px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">Search</h1>

        <form action="/search" method="GET" class="mt-7 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex-1">
                <x-input name="q" label="Search the site" :value="$query"
                         placeholder="Membership, events, news" />
            </div>
            <x-button type="submit">Search</x-button>
        </form>

        @if ($query === '')
            {{-- App Flow P-11 — an empty query is a prompt, not an error. --}}
            <p class="t-body text-ink-muted mt-8 measure-ui">
                Enter a word or phrase to search news, pages and questions.
            </p>
        @elseif ($results->isEmpty())
            {{-- Not presented as a failure (P-11). --}}
            <div class="mt-8">
                <x-empty-state heading="Nothing matched that search">
                    No news, page or question mentions {{ $query }}. Try a different
                    word, or browse the news section.
                    <x-slot:action>
                        <x-button href="/news" variant="secondary">Browse news</x-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @else
            <p class="t-body-sm text-ink-muted mt-8">
                {{ $results->count() }} {{ Str::plural('result', $results->count()) }} for {{ $query }}
            </p>

            <ul class="mt-5 divide-y divide-rule border-t border-rule">
                @foreach ($results as $result)
                    <li class="py-5 flex flex-col gap-2">
                        <a href="{{ $result->url }}" class="t-h4 text-forest-800 underline underline-offset-2">
                            {{ $result->title }}
                        </a>
                        <span class="t-micro text-ink-muted">{{ $result->typeLabel() }}</span>
                        @if ($result->excerpt)
                            <p class="t-body text-ink measure-ui">{{ $result->excerpt }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts::public>
