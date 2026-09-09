{{-- P-01 Home — FR-1.2, UI brief §7 --}}
<x-layouts::public :description="'The Association of Data Protection Compliance Organizations of Nigeria — membership, events and news for data protection professionals.'">

    {{-- The hero is typographic. No image, no illustration (§7): the
         association's full name set large in the serif is the most credible
         thing it can lead with, and it costs nothing in LCP. --}}
    <section class="mx-auto max-w-app px-4 sm:px-6 pt-8 md:pt-10 pb-9 md:pb-10">
        <h1 class="t-display text-forest-800 max-w-[18ch]">
            Association of Data Protection Compliance Organizations of Nigeria
        </h1>

        <p class="t-body-lg text-ink mt-6 measure-ui">
            The professional body for licensed Data Protection Compliance
            Organizations and Data Protection Officers working under the Nigeria
            Data Protection Act.
        </p>

        <div class="mt-7 flex flex-wrap items-center gap-4">
            <x-button href="/membership" size="lg">Join the association</x-button>
            <a href="/membership" class="t-body text-forest-700 underline underline-offset-2">
                See membership categories
            </a>
        </div>
    </section>

    {{-- AC-F1 — exactly the three latest published posts.
         App Flow P-01: with no published posts the band is REMOVED entirely,
         not shown with placeholders. An empty slot reads as neglect. --}}
    @if ($posts->isNotEmpty())
        <section class="bg-surface py-9 md:py-10" aria-labelledby="latest">
            <div class="mx-auto max-w-app px-4 sm:px-6">
                <div class="flex items-baseline justify-between gap-4">
                    <h2 id="latest" class="t-h2 text-forest-800">Latest</h2>
                    <a href="/news" class="t-body text-forest-700 underline underline-offset-2">View all news</a>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($posts as $post)
                        <x-news-card :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Events arrive in Phase 12. The band is absent rather than empty, for
         the same reason as above. --}}
</x-layouts::public>
