{{--
    Footer — UI brief §5.3, App Flow G-04; PRD FR-1.5, FR-12.4

    Secondary navigation, contact summary, legal links, cookie preferences,
    newsletter link.

    The cookie preferences link is not optional decoration. FR-12.1 requires
    the visitor's choice to be RE-CALLABLE at any time, which needs a permanent
    control — a first-visit banner alone does not satisfy it (G-02).
--}}

<footer class="bg-forest-900 text-white mt-10 md:mt-11">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-9">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div class="flex flex-col gap-3">
                <span class="t-h4 font-semibold">ALDAPCON</span>
                <p class="t-body-sm text-white/80 measure-ui">
                    Association of Data Protection Compliance Organizations of Nigeria.
                </p>
            </div>

            <nav class="flex flex-col gap-2" aria-label="Footer">
                <span class="t-caption text-white/70">Association</span>
                <a href="/about" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">About us</a>
                <a href="/leadership" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Leadership</a>
                <a href="/membership" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Membership</a>
                <a href="/contact" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Contact</a>
            </nav>

            <nav class="flex flex-col gap-2" aria-label="Legal">
                <span class="t-caption text-white/70">Legal</span>
                <a href="/privacy-policy" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Privacy policy</a>
                <a href="/cookie-policy" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Cookie policy</a>
                <a href="/terms" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">Terms of use</a>
                {{-- FR-12.1 — the re-callable control. Wired in Phase 14. --}}
                <button type="button"
                        x-on:click="$dispatch('open-cookie-preferences')"
                        class="t-body-sm text-white/90 hover:text-white underline underline-offset-2 text-left min-h-control">
                    Cookie preferences
                </button>
            </nav>
        </div>

        <div class="mt-8 pt-5 border-t border-white/15">
            <p class="t-body-sm text-white/70">
                &copy; {{ date('Y') }} ALDAPCON. All rights reserved.
            </p>
        </div>
    </div>
</footer>
