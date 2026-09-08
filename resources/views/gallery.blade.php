{{--
    Component gallery — plan Phase 2 completion criterion:
    "A component gallery page renders every component in every state."

    This is a development surface, not a product page. The route is registered
    only outside production (routes/web.php).

    It exists to make three things checkable by eye and by tool, rather than
    asserted: that every component has all of its applicable states, that the
    contrast figures in UI brief §2.2 hold in the implementation, and that the
    whole system is keyboard-operable.
--}}

<x-layouts::public title="Component gallery">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-8 md:py-10 flex flex-col gap-11">

        <header class="flex flex-col gap-3">
            <h1 class="t-display text-forest-800">Component gallery</h1>
            <p class="t-body-lg text-ink measure-prose">
                Every component in the design system, in every state it ships.
                Phase 2 of the build. Not reachable in production.
            </p>
        </header>

        {{-- ================= TYPE ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-type">
            <h2 id="s-type" class="t-h2 text-forest-800">Type scale</h2>
            <p class="t-body text-ink-muted measure-ui">
                Base 16px. Mobile ratio 1.200, desktop 1.250. Reading matter in
                the serif, official business in the working sans.
            </p>

            <div class="flex flex-col gap-4 bg-white border border-rule p-5">
                <p class="t-display">Display — home hero only</p>
                <p class="t-h1">Heading 1 — page and post titles</p>
                <p class="t-h2">Heading 2 — section headings</p>
                <p class="t-h3">Heading 3 — sub-sections</p>
                <p class="t-h4">Heading 4 — card and panel titles (sans)</p>
                <p class="t-body-lg measure-prose">Body large — article body and About. Serif at 1.65 line height, because serifs need the extra air and the article measure is longer.</p>
                <p class="t-body measure-ui">Body — interface prose and forms.</p>
                <p class="t-body-sm text-ink-muted">Body small — help text and metadata.</p>
                <p class="t-caption text-ink-muted">Caption — table headers and field labels</p>
                <p class="t-micro text-ink-muted">Micro — status badges and tags. The floor; nothing smaller ships.</p>
                <p class="t-record-number text-brass-700">DPCO-2026-00034</p>
                <p class="t-body figure">Tabular figures: ₦25,000 · ₦1,250,000 · 14 March 2027</p>
            </div>
        </section>

        {{-- ================= COLOUR ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-colour">
            <h2 id="s-colour" class="t-h2 text-forest-800">Colour</h2>
            <p class="t-body text-ink-muted measure-ui">
                Ratios are the calculated figures from UI brief §2.2. If the
                implementation and the table ever disagree, the table is the
                accessibility claim and the implementation is the defect.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ([
                    ['Ink on paper', 'bg-paper text-ink', '16.18:1', 'AAA'],
                    ['Ink on surface', 'bg-surface text-ink', '14.98:1', 'AAA'],
                    ['Ink muted on paper', 'bg-paper text-ink-muted', '6.54:1', 'AAA'],
                    ['Forest 700 on white', 'bg-white text-forest-700', '6.67:1', 'AAA'],
                    ['Forest 800 on white', 'bg-white text-forest-800', '8.89:1', 'AAA'],
                    ['White on forest 700', 'bg-forest-700 text-white', '6.67:1', 'AAA'],
                    ['White on forest 900', 'bg-forest-900 text-white', '12.57:1', 'AAA'],
                    ['Brass 700 on white', 'bg-white text-brass-700', '6.10:1', 'AAA'],
                    ['Flag green on white', 'bg-white text-flag', '4.58:1', 'AA — not body text'],
                ] as [$name, $classes, $ratio, $verdict])
                    <div class="border border-rule">
                        <div class="{{ $classes }} p-5">
                            <p class="t-body">{{ $name }}</p>
                            <p class="t-body-sm figure">The quick brown fox 12345</p>
                        </div>
                        <div class="bg-white px-5 py-3 border-t border-rule">
                            <p class="t-caption text-ink figure">{{ $ratio }} — {{ $verdict }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================= BUTTONS ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-buttons">
            <h2 id="s-buttons" class="t-h2 text-forest-800">Buttons</h2>

            <div class="bg-white border border-rule p-5 flex flex-col gap-6">
                <div class="flex flex-col gap-3">
                    <p class="t-caption text-ink-muted">Variants — default state</p>
                    <div class="flex flex-wrap items-start gap-3">
                        <x-button variant="primary">Pay ₦25,000 and continue</x-button>
                        <x-button variant="secondary">Go back</x-button>
                        <x-button variant="quiet">Resend link</x-button>
                        <x-button variant="destructive">Deactivate membership</x-button>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <p class="t-caption text-ink-muted">Sizes — 36px, 44px, 52px</p>
                    <div class="flex flex-wrap items-start gap-3">
                        <x-button size="sm">Small</x-button>
                        <x-button size="md">Medium</x-button>
                        <x-button size="lg">Large — payment actions</x-button>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <p class="t-caption text-ink-muted">Loading — keeps its width, announces to screen readers</p>
                    <div class="flex flex-wrap items-start gap-3">
                        <x-button :loading="true">Confirming your payment</x-button>
                        <x-button variant="secondary" :loading="true">Saving</x-button>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <p class="t-caption text-ink-muted">
                        Disabled — always accompanied by what would enable it
                    </p>
                    <div class="flex flex-wrap items-start gap-6">
                        <x-button :disabled="true" disabledReason="Select a membership category to continue.">
                            Continue
                        </x-button>
                        <x-button variant="destructive" :disabled="true" disabledReason="Only a Super Admin can delete a member record.">
                            Delete member
                        </x-button>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <p class="t-caption text-ink-muted">As a link</p>
                    <x-button href="/membership" variant="secondary">See membership categories</x-button>
                </div>
            </div>
        </section>

        {{-- ================= FORM CONTROLS ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-forms">
            <h2 id="s-forms" class="t-h2 text-forest-800">Form controls</h2>

            <div class="bg-white border border-rule p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input name="g_name" label="Full name" placeholder="Adaeze Okonkwo" />

                <x-input name="g_phone" label="Phone number" type="tel"
                         placeholder="08012345678"
                         help="We use this only for membership matters." />

                <x-input name="g_email_err" label="Email address" type="email"
                         value="not-an-email"
                         error="Enter an email address in the format name@example.com" />

                <x-input name="g_locked" label="Email address" value="adaeze@example.com"
                         :readonly="true"
                         readonlyReason="Your email is the anchor for your payments. An administrator can change it." />

                <x-input name="g_optional" label="Job title" :optional="true" />

                <x-select name="g_state" label="State"
                          placeholder="Choose a state"
                          :options="['lagos' => 'Lagos', 'fct' => 'Federal Capital Territory', 'rivers' => 'Rivers']" />

                <div class="md:col-span-2 flex flex-col gap-5 pt-2 border-t border-rule">
                    <x-checkbox name="g_check" label="Send me event reminders"
                                help="An ordinary preference, not a consent." />

                    <x-consent-checkbox name="g_consent_processing" :required="true"
                                        purpose="Required to process your membership application.">
                        I agree to ALDAPCON processing my personal data as described in the Privacy Policy.
                    </x-consent-checkbox>

                    <x-consent-checkbox name="g_consent_marketing"
                                        purpose="Optional. You can withdraw this at any time.">
                        I would like to receive the ALDAPCON newsletter.
                    </x-consent-checkbox>
                </div>
            </div>
        </section>

        {{-- ================= FILE UPLOAD ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-upload">
            <h2 id="s-upload" class="t-h2 text-forest-800">File upload</h2>
            <p class="t-body text-ink-muted measure-ui">
                The only file input in the member-facing product. Limits are
                stated above the control, and the keyboard button is real —
                a drag-only zone is not operable.
            </p>

            <div class="bg-white border border-rule p-5 flex flex-col gap-6">
                <x-file-upload name="g_certificate" label="NDPC licence certificate"
                               help="Upload the certificate issued to your organisation. Membership begins after an administrator has reviewed it." />

                <x-file-upload name="g_certificate_err" label="NDPC licence certificate"
                               error="That file is 11.4 MB. The maximum is 8 MB." />
            </div>
        </section>

        {{-- ================= STATUS ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-status">
            <h2 id="s-status" class="t-h2 text-forest-800">Status badges</h2>
            <p class="t-body text-ink-muted measure-ui">
                Three signals each — text, shape and colour. The shape is
                load-bearing: green and amber are the classic deuteranopia
                confusion pair, and they carry the two states that drive
                renewal revenue.
            </p>

            <div class="bg-white border border-rule p-5 flex flex-wrap items-center gap-4">
                <x-badge status="active" />
                <x-badge status="expiring" />
                <x-badge status="expired" />
                <x-badge status="suspended" />
                <x-badge status="pending" />
            </div>
        </section>

        {{-- ================= ALERTS ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-alerts">
            <h2 id="s-alerts" class="t-h2 text-forest-800">Inline alerts</h2>

            <div class="flex flex-col gap-4">
                <x-alert variant="success" title="Payment confirmed">
                    Your receipt is on its way to adaeze@example.com.
                </x-alert>

                <x-alert variant="warning" title="Your membership expires in 25 days">
                    Renewing before 14 March keeps your expiry date running from
                    then, not from the day you pay.
                </x-alert>

                <x-alert variant="error" title="We could not take that payment">
                    No money has left your account. You can try again, and your
                    details are still here.
                </x-alert>

                <x-alert variant="info">
                    Membership runs for twelve months from the day it is activated.
                </x-alert>
            </div>
        </section>

        {{-- ================= EMPTY STATES ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-empty">
            <h2 id="s-empty" class="t-h2 text-forest-800">Empty states</h2>

            <div class="bg-white border border-rule p-5 divide-y divide-rule">
                <x-empty-state heading="No events yet">
                    Upcoming conferences and workshops will appear here once the
                    association publishes them.
                    <x-slot:action>
                        <x-button variant="secondary" href="/news">Read the latest news</x-button>
                    </x-slot:action>
                </x-empty-state>

                <x-empty-state heading="Nothing awaiting registration">
                    Everybody who has paid has completed their registration. This
                    queue being empty is good news.
                </x-empty-state>
            </div>
        </section>

        {{-- ================= RECORD PANEL ================= --}}
        <section class="flex flex-col gap-5" aria-labelledby="s-record">
            <h2 id="s-record" class="t-h2 text-forest-800">Membership Record panel</h2>
            <p class="t-body text-ink-muted measure-ui">
                Three placements only — the welcome screen, the top of the
                portal dashboard, and the printed receipt. Its authority
                depends on that scarcity. Never shown to a pending or rejected
                applicant.
            </p>

            <div class="max-w-form">
                <x-record-panel
                    number="DPCO-2026-00034"
                    category="Licensed DPCO — Corporate"
                    status="active"
                    validUntil="14 March 2027" />
            </div>
        </section>

    </div>
</x-layouts::public>
