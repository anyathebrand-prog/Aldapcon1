{{--
    404 — UI brief §7; PRD AC-F1, App Flow P-15

    container-prose, a serif h1, one sentence, and REAL ROUTES OUT: search plus
    the three most likely destinations. AC-F1 requires the page to offer
    navigation rather than being a dead end.

    §7 is explicit about what this must not be: no large numerals as
    decoration, no illustration. The number is in the heading text, once,
    because it is information rather than ornament.
--}}

<x-layouts::public title="Page not found">
    <div class="mx-auto max-w-prose px-4 sm:px-6 py-10 md:py-11">
        <h1 class="t-h1 text-forest-800">We could not find that page</h1>

        <p class="t-body-lg text-ink mt-5 measure-prose">
            The page you asked for does not exist, or it may have moved. Nothing
            has gone wrong with your account.
        </p>

        <form action="/search" method="GET" class="mt-8 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex-1">
                <x-input name="q" label="Search the site" placeholder="Membership, events, news" />
            </div>
            <x-button type="submit">Search</x-button>
        </form>

        <nav class="mt-8 flex flex-col gap-2" aria-label="Suggested pages">
            <span class="t-caption text-ink-muted">Or try one of these</span>
            <a href="/" class="t-body text-forest-700 underline underline-offset-2">Home</a>
            <a href="/membership" class="t-body text-forest-700 underline underline-offset-2">Membership categories and fees</a>
            <a href="/news" class="t-body text-forest-700 underline underline-offset-2">News</a>
            <a href="/contact" class="t-body text-forest-700 underline underline-offset-2">Contact the association</a>
        </nav>
    </div>
</x-layouts::public>
