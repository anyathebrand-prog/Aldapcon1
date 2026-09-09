{{-- P-09 FAQ — FR-1.1, App Flow P-09 --}}
<x-layouts::public title="Frequently asked questions"
                   description="Common questions about ALDAPCON membership, events and the association.">
    <div class="mx-auto max-w-prose px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">Frequently asked questions</h1>

        @if ($groups->isEmpty())
            <x-empty-state heading="No questions published yet">
                The association is preparing answers to the questions it is asked most.
                <x-slot:action>
                    <x-button href="/contact" variant="secondary">Ask us directly</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            @foreach ($groups as $group => $faqs)
                <section class="mt-8" aria-labelledby="faq-{{ Str::slug($group) }}">
                    <h2 id="faq-{{ Str::slug($group) }}" class="t-h2 text-forest-800">{{ $group }}</h2>

                    <div class="mt-4 divide-y divide-rule border-t border-rule">
                        @foreach ($faqs as $faq)
                            {{-- Native <details>: expand and collapse with no
                                 JavaScript, keyboard operable for free, and
                                 reachable by the browser in-page search. --}}
                            <details class="py-4">
                                <summary class="t-h4 text-ink cursor-pointer min-h-control flex items-center">
                                    {{ $faq->question }}
                                </summary>
                                <div class="t-body text-ink mt-3 measure-ui">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </div>
</x-layouts::public>
