{{--
    Text input — UI brief §5.2, §6, §10

    Rules this component exists to enforce, because each is easy to lose:

    - The label is ALWAYS visible and above the field. Placeholder-as-label
      disappears on focus and fails cognitive accessibility outright
      (§5.2, §11 never-6).
    - Placeholders show FORMAT only, never the label: `08012345678`.
    - Errors carry border, icon and text, wired with aria-describedby and
      aria-invalid. Never colour alone (§10, §11 never-7).
    - Read-only fields use `readonly`, NOT `disabled` (F-7): disabled controls
      are skipped by some screen readers and typically fail contrast. They get
      a lock icon and a one-line explanation of who can change the value,
      because an unexplained locked field generates a support email every time
      (§11 always-10).
--}}

@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'help' => null,
    'error' => null,
    'required' => false,
    'optional' => false,
    'readonly' => false,
    'readonlyReason' => null,
    'placeholder' => null,
])

@php
    $id = $attributes->get('id', $name);
    $helpId = $help ? $id.'-help' : null;
    $errorId = $error ? $id.'-error' : null;
    $lockId = $readonly && $readonlyReason ? $id.'-lock' : null;
    $describedBy = collect([$helpId, $errorId, $lockId])->filter()->implode(' ');
@endphp

<div class="flex flex-col gap-2">
    <label for="{{ $id }}" class="t-caption text-ink">
        {{ $label }}
        {{-- §5.2 — whichever set is smaller gets the marker. Required state is
             conveyed in text, not by an asterisk alone (§10). --}}
        @if ($optional)
            <span class="text-ink-muted font-normal">(optional)</span>
        @endif
    </label>

    <div class="relative">
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($readonly) readonly @endif
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => implode(' ', [
                'w-full h-[48px] px-3 py-3 rounded-sm t-body',
                'border transition-colors duration-micro ease-enter',
                'focus:border-forest-700',
                $readonly ? 'bg-surface text-ink pr-10 cursor-default' : 'bg-white text-ink',
                $error ? 'border-error-border' : 'border-border',
            ])]) }}
        />

        @if ($readonly)
            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-muted"
                 viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <rect x="3.5" y="7" width="9" height="6.5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                <path d="M5.5 7V5a2.5 2.5 0 1 1 5 0v2" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        @endif
    </div>

    @if ($readonly && $readonlyReason)
        <p id="{{ $lockId }}" class="t-body-sm text-ink-muted">{{ $readonlyReason }}</p>
    @endif

    @if ($help)
        <p id="{{ $helpId }}" class="t-body-sm text-ink-muted measure-ui">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $errorId }}" class="t-body-sm text-error-fg flex items-start gap-2">
            <svg class="h-4 w-4 shrink-0 mt-0.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 5v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="8" cy="11" r="0.75" fill="currentColor"/>
            </svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
