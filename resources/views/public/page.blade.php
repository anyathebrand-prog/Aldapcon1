{{-- P-02, P-12 to P-14 — editable pages and legal documents. FR-1.1, FR-12.4 --}}
<x-layouts::public :title="$page->meta_title ?: $page->title"
                   :description="$page->meta_description">
    <div class="mx-auto max-w-prose px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">{{ $page->title }}</h1>

        {{-- Admin-editable, so it must tolerate long and short bodies
             (App Flow P-02). --}}
        <div class="t-body-lg text-ink mt-7 prose-links flex flex-col gap-5">
            {!! $page->body !!}
        </div>
    </div>
</x-layouts::public>
