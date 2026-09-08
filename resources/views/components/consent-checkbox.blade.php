{{--
    Consent checkbox — UI brief §5.2, F-6; PRD FR-12.2

    A distinct component from <x-checkbox>, and it must stay distinct.

    NDPA requires consent that is unticked by default, unbundled, and
    evidenced. So:

    - 24x24 box, 2px border, SQUARE (radius-none) — visually unlike the
      ordinary checkbox, because it is legally unlike it.
    - Never a toggle switch (§11 never-5). A toggle implies a default-on state
      and reads as a preference rather than a permission. F-6 flags that this
      will be proposed as a visual improvement at some point; the answer is no.
    - One purpose per control (§11 never-18). Two purposes in one checkbox is
      bundled consent and is not consent at all.
    - No `checked` prop exists on this component AT ALL. Pre-ticking consent
      is not a feature that can be reached by passing an argument.

    The exact label text shown here is what gets snapshotted into
    consent_records.consent_text_snapshot (Schema §2.8), so it is the evidence.
    Change it and you change the record.
--}}

@props([
    'name',
    'purpose',
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $errorId = $error ? $id.'-error' : null;
@endphp

<div class="flex flex-col gap-1">
    {{-- 44px tappable label area (§5.2). --}}
    <div class="flex items-start gap-3 py-2">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="1"
            @if ($required) required @endif
            @if ($error) aria-invalid="true" @endif
            @if ($errorId) aria-describedby="{{ $errorId }}" @endif
            {{ $attributes->merge(['class' => implode(' ', [
                'mt-0.5 h-6 w-6 shrink-0 border-2 bg-white text-forest-700',
                'rounded-none focus:ring-0 focus:ring-offset-0',
                $error ? 'border-error-border' : 'border-border',
            ])]) }}
        />
        <label for="{{ $id }}" class="t-body text-ink measure-ui cursor-pointer min-h-control flex items-center">
            {{ $slot }}
        </label>
    </div>

    <p class="t-body-sm text-ink-muted pl-9 measure-ui">{{ $purpose }}</p>

    @if ($error)
        <p id="{{ $errorId }}" class="t-body-sm text-error-fg pl-9">{{ $error }}</p>
    @endif
</div>
