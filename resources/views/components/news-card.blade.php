{{--
    News card — UI brief §5.4.

    "It behaves like a piece of editorial, because it is." No border, no
    shadow, no container box: image, title, date, excerpt, separated from its
    siblings by whitespace and a rule on mobile.

    Deliberately unlike the event card, which is structurally different at a
    glance. Identical cards are the failure mode §5.4 exists to prevent.
--}}

@props(['post'])

<article class="flex flex-col gap-3 pb-6 border-b border-rule md:border-b-0 md:pb-0">
    @if ($post->featuredImage)
        <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->path) }}"
             alt="{{ $post->featuredImage->alt_text }}"
             width="{{ $post->featuredImage->width }}"
             height="{{ $post->featuredImage->height }}"
             loading="lazy"
             class="w-full rounded-lg aspect-[16/9] object-cover" />
    @endif

    <h3 class="t-h4 font-serif">
        <a href="/news/{{ $post->slug }}" class="text-forest-800 hover:underline underline-offset-2">
            {{ $post->title }}
        </a>
    </h3>

    {{-- §3.4 — no metadata strings joined by middle dots. Separate elements
         with spacing. --}}
    <p class="t-body-sm text-ink-muted figure">
        {{ $post->published_at?->timezone('Africa/Lagos')->format('j F Y') }}
    </p>

    @if ($post->excerpt)
        <p class="t-body text-ink measure-ui">{{ $post->excerpt }}</p>
    @endif
</article>
