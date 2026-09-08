{{-- P-03 Leadership — FR-1.3, AC-F1 --}}
<x-layouts::public title="Leadership"
                   description="The executive council of the Association of Data Protection Compliance Organizations of Nigeria.">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">Executive council</h1>

        @if ($profiles->isEmpty())
            {{-- App Flow P-03: the page is not linked in navigation until at
                 least one profile exists, so reaching it empty is a direct
                 visit rather than a dead end offered to a visitor. --}}
            <x-empty-state heading="Council profiles are being prepared">
                The association will publish its executive council here shortly.
                <x-slot:action>
                    <x-button href="/contact" variant="secondary">Contact the association</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <ul class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
                @foreach ($profiles as $profile)
                    <li class="flex gap-5">
                        @if ($profile->photo)
                            <img src="{{ Storage::disk($profile->photo->disk)->url($profile->photo->path) }}"
                                 alt="{{ $profile->photo->alt_text }}"
                                 width="96" height="96" loading="lazy"
                                 class="h-24 w-24 rounded-lg object-cover shrink-0" />
                        @else
                            {{-- Never a broken image (P-03): a neutral
                                 initial-based placeholder instead. --}}
                            <div class="h-24 w-24 rounded-lg bg-surface border border-rule shrink-0 flex items-center justify-center"
                                 aria-hidden="true">
                                <span class="t-h3 text-ink-muted">{{ $profile->initials() }}</span>
                            </div>
                        @endif

                        <div class="flex flex-col gap-2">
                            <h2 class="t-h4">{{ $profile->name }}</h2>
                            <p class="t-body-sm text-ink-muted">{{ $profile->position }}</p>
                            @if ($profile->bio)
                                <p class="t-body text-ink measure-ui">{{ $profile->bio }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts::public>
