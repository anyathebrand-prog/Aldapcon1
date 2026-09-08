{{--
    Cookie consent banner — SHELL ONLY. UI brief F-8; PRD FR-12.1

    Phase 2 builds the presentation; Phase 14 wires the behaviour — persisting
    the choice in a first-party cookie, gating non-essential scripts, and the
    preferences dialog behind the footer control.

    Two decisions baked into the markup:

    NOT A BLOCKING MODAL (F-8). A modal on first visit buries the hero, hurts
    LCP, and greets a prospective member with a legal interruption during the
    eight seconds in which they decide whether this association is real. It is
    bottom-anchored and non-blocking.

    ACCEPT AND REJECT CARRY EQUAL VISUAL WEIGHT (F-8, §11 always). Unequal
    weighting is a dark pattern, and indefensible for this client in
    particular. Both are the same variant, the same size, in the same row. Any
    future request to make Accept more prominent should be refused on the
    record.

    FR-12.1 also requires the choice to be stored client-side only. There is
    deliberately no per-visitor server table (Schema §2.8): identifying an
    anonymous visitor in order to record their preference not to be tracked
    would defeat the purpose.
--}}

<div x-data="{ shown: true }"
     x-show="shown"
     x-cloak
     class="fixed inset-x-0 bottom-0 z-30 bg-white border-t border-border"
     role="region"
     aria-label="Cookie choices">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
            <p class="t-body-sm text-ink measure-ui flex-1">
                We use essential cookies to make this site work. With your agreement we
                also use analytics cookies to understand how it is used.
                <a href="/cookie-policy" class="text-forest-700 underline underline-offset-2">Read our cookie policy</a>.
            </p>

            {{-- Equal weight. Same variant, same size, same row. --}}
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" x-on:click="shown = false"
                        class="inline-flex items-center justify-center min-h-control px-5 py-3 rounded-md t-body font-semibold bg-transparent text-forest-800 border border-border hover:bg-forest-50">
                    Reject non-essential
                </button>
                <button type="button" x-on:click="shown = false"
                        class="inline-flex items-center justify-center min-h-control px-5 py-3 rounded-md t-body font-semibold bg-transparent text-forest-800 border border-border hover:bg-forest-50">
                    Accept all
                </button>
                <button type="button"
                        class="t-body-sm text-forest-700 underline underline-offset-2 min-h-control px-2">
                    Preferences
                </button>
            </div>
        </div>
    </div>
</div>
