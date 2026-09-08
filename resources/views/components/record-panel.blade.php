{{--
    Membership Record panel — UI brief §1.3, §5.4, F-12, F-14

    The one element that carries the design's personality. Everything around it
    is quiet, disciplined and unremarkable on purpose.

    It is a RECORD, not a control: no hover state, not clickable, no shadow.
    Square corners while nothing else on the page is square, so it reads as a
    document among interface elements.

    THREE PLACEMENTS ONLY (F-12):
        1. the welcome screen (J-07)
        2. the top of the portal dashboard (M-01)
        3. the printed receipt

    Its authority depends on that scarcity. Reused as a general panel style —
    for events, for news, for admin widgets — it stops reading as a document
    and the design loses its only distinctive element.

    NEVER shown to a pending or rejected applicant (§11 never-19, App Flow
    J-10). It is reserved for issued membership; showing it to somebody
    awaiting verification would tell them they are a member when they are not.

    F-14: the receipt is a dompdf render, which supports a narrow CSS subset
    and will carry neither this border treatment nor Archivo's width axis. A
    separate print variant is required and is built in Phase 8 alongside the
    receipt — not here, and not by adding a `print` prop to this component.

    Renamed from "block" to "panel" per audit N-4, so it is never confused with
    the downloadable digital membership card excluded by PRD §3.2. This is an
    on-screen panel; there is no saveable artefact in V1.
--}}

@props([
    'number',
    'category',
    'status' => 'active',
    'validUntil',
])

<div {{ $attributes->merge(['class' => 'bg-white border-2 border-forest-900 rounded-none']) }}>
    {{-- The one place flag green appears at full strength inside a component
         (§2.3). Identity accent, never a status and never text. --}}
    <div class="h-[3px] bg-flag" aria-hidden="true"></div>

    <div class="p-5 sm:p-6">
        <p class="t-caption text-ink-muted">Membership number</p>

        {{-- Brass, wide-set, tabular. The figure reads as issued rather than
             rendered (§1.3). --}}
        <p class="t-record-number text-brass-700 mt-2 break-words">{{ $number }}</p>

        <hr class="border-0 border-t border-rule my-5" />

        {{-- Two-column definition list at >=600px, stacked below (§5.5).
             Never scrolls sideways (§8). --}}
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div class="flex flex-col gap-1">
                <dt class="t-caption text-ink-muted">Category</dt>
                <dd class="t-body text-ink">{{ $category }}</dd>
            </div>

            <div class="flex flex-col gap-1">
                <dt class="t-caption text-ink-muted">Status</dt>
                <dd><x-badge :status="$status" /></dd>
            </div>

            <div class="flex flex-col gap-1">
                <dt class="t-caption text-ink-muted">Valid until</dt>
                <dd class="t-body text-ink figure">{{ $validUntil }}</dd>
            </div>
        </dl>
    </div>
</div>
