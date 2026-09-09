{{--
    J-01 Category selection — FR-3.1, App Flow J-01, UI brief §5.4.

    "Route the applicant into the right category before any data is
    collected."

    The category card is the ONLY card in the system a user can select, so it
    is the only one that looks selectable (§5.4): a bordered control with
    radio semantics, not a piece of content.

    Continue is disabled until a category is chosen, and — per §6 — a disabled
    primary action always states what would enable it.
--}}
<x-layouts::form title="Join the association">
    <h1 class="t-h1 text-forest-800">Join the association</h1>

    @if ($categories->isEmpty())
        {{-- App Flow J-01: the blocked state, as on P-04. --}}
        <div class="mt-7">
            <x-alert variant="warning" title="Membership opens shortly">
                Categories and fees are being finalised.
            </x-alert>
        </div>
    @else
        <p class="t-body text-ink mt-5 measure-ui">
            Choose the category you qualify for. You will pay first, then complete
            your registration details.
        </p>

        <form method="GET" action="/join" class="mt-7 flex flex-col gap-5"
              x-data="{ selected: null }">

            <fieldset class="flex flex-col gap-4">
                <legend class="t-caption text-ink mb-2">Membership category</legend>

                @foreach ($categories as $category)
                    <label class="flex gap-4 p-5 rounded-sm border cursor-pointer transition-colors duration-micro ease-enter"
                           :class="selected === '{{ $category->slug }}'
                               ? 'border-forest-700 border-2 bg-forest-100'
                               : 'border-border bg-white hover:bg-forest-50'">
                        <input type="radio" name="category" value="{{ $category->slug }}"
                               x-model="selected"
                               class="mt-1 h-5 w-5 shrink-0 border-2 border-border text-forest-700 focus:ring-0 focus:ring-offset-0" />

                        <span class="flex flex-col gap-2">
                            <span class="t-h4 text-forest-800">{{ $category->name }}</span>
                            <span class="t-body figure text-ink">
                                {{ $category->feeInNaira() }} a year
                            </span>
                            <span class="t-body-sm text-ink-muted measure-ui">
                                {{ $category->eligibility }}
                            </span>

                            @if ($category->requires_verification)
                                <span class="t-body-sm text-ink-muted measure-ui">
                                    Requires your NDPC licence certificate. Membership begins
                                    after an administrator has reviewed it.
                                </span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <div class="pt-2">
                {{--
                    The payment flow arrives in Phase 9a. Until then this is
                    deliberately disabled with the reason stated, rather than
                    leading somewhere that does not exist — a button that
                    silently does nothing is worse than an absent one
                    (App Flow D-04, on the same principle).
                --}}
                <x-button type="submit" size="lg" :disabled="true"
                          disabledReason="Online payment opens when the association's payment account is live.">
                    Continue to payment
                </x-button>
            </div>
        </form>
    @endif

    <hr class="border-0 border-t border-rule my-8" />

    <p class="t-body text-ink">
        Want to compare categories first?
        <a href="/membership" class="text-forest-700 underline underline-offset-2">See the full details</a>.
    </p>
</x-layouts::form>
