{{-- P-06 News post — FR-7.4, UI brief §7 --}}
<x-layouts::public :title="$post->meta_title ?: $post->title"
                   :description="$post->meta_description ?: $post->excerpt">

    {{-- container-prose at 680px, serif body-lg at 1.65. No sidebar, no
         floating share bar, no related-posts rail alongside the text (§7). --}}
    <article class="mx-auto max-w-prose px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">{{ $post->title }}</h1>

        <div class="mt-4 flex flex-wrap items-center gap-4">
            <p class="t-body-sm text-ink-muted figure">
                {{ $post->published_at?->timezone('Africa/Lagos')->format('j F Y') }}
            </p>
            <p class="t-body-sm text-ink-muted">{{ $post->author?->full_name }}</p>
        </div>

        @if ($post->featuredImage)
            <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->path) }}"
                 alt="{{ $post->featuredImage->alt_text }}"
                 width="{{ $post->featuredImage->width }}"
                 height="{{ $post->featuredImage->height }}"
                 class="w-full rounded-lg mt-7" />
        @endif

        {{-- Sanitised on save, not on render (TRD §6.1). --}}
        <div class="t-body-lg text-ink mt-7 prose-links flex flex-col gap-5">
            {!! $post->body !!}
        </div>

        @if ($post->terms->isNotEmpty())
            <ul class="mt-8 flex flex-wrap gap-2">
                @foreach ($post->terms as $term)
                    <li>
                        <a href="/news?category={{ $term->slug }}"
                           class="t-micro px-2 py-1 rounded-sm border border-border text-ink hover:bg-forest-50 inline-block">
                            {{ $term->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>

    {{-- Share links and related posts sit AFTER the article (§7). --}}
    @if ($related->isNotEmpty())
        <section class="bg-surface py-9" aria-labelledby="related">
            <div class="mx-auto max-w-app px-4 sm:px-6">
                <h2 id="related" class="t-h2 text-forest-800">More from ALDAPCON</h2>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($related as $item)
                        <x-news-card :post="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::public>
