{{--
    Button — UI brief §5.1, §6

    Four variants, three sizes. One primary per screen region.

    Copy rule (§5.1, §11 never-15): the label states the OUTCOME and keeps the
    same verb through a flow. "Pay ₦25,000 and continue", never "Submit",
    never "Click here", never a trailing arrow character. That is enforced by
    review, not by this component — but the component makes no attempt to
    append decoration of its own.

    Disabled (§6): a disabled primary action must be accompanied by text
    saying what would enable it. Pass `disabledReason` and it is rendered and
    wired to the button with aria-describedby. A dead button with no
    explanation is a dead end.
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'disabled' => false,
    'disabledReason' => null,
])

@php
    $variants = [
        // The only large filled green area on any screen (§2.3).
        'primary' => 'bg-forest-700 text-white border border-transparent hover:bg-forest-800',
        'secondary' => 'bg-transparent text-forest-800 border border-border hover:bg-forest-50',
        // Tertiary actions inside tables and rows. Underlined on hover.
        'quiet' => 'bg-transparent text-forest-700 border border-transparent hover:underline underline-offset-2',
        // Filled red only inside a confirmation dialog, never in the page.
        'destructive' => 'bg-transparent text-error-fg border border-error-border hover:bg-error-bg',
    ];

    $sizes = [
        'sm' => 'min-h-[36px] px-4 py-2 t-body-sm',   // admin table rows
        'md' => 'min-h-control px-5 py-3 t-body',      // default, 44px
        'lg' => 'min-h-control-lg px-6 py-3 t-body',   // payment actions, mobile sticky bar
    ];

    $isDisabled = $disabled || $loading;
    $reasonId = $disabledReason ? 'btn-reason-'.Str::random(6) : null;

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2 rounded-md font-semibold',
        'transition-colors duration-micro ease-enter',
        'active:translate-y-px', // §6 — 1px press, no scale transforms
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $isDisabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : '',
    ]);
@endphp

<div class="inline-flex flex-col items-start gap-1">
    @if ($href && ! $isDisabled)
        <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
            {{ $slot }}
        </a>
    @else
        <button
            type="{{ $type }}"
            @if ($isDisabled) disabled aria-disabled="true" @endif
            @if ($reasonId) aria-describedby="{{ $reasonId }}" @endif
            {{ $attributes->merge(['class' => $classes]) }}
        >
            @if ($loading)
                {{-- §6: the button keeps its width and stays focused. The
                     spinner is decorative; the live region carries the meaning
                     for anyone who cannot see it. --}}
                <svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" opacity="0.25" />
                    <path d="M14 8a6 6 0 0 0-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span aria-live="polite">{{ $slot }}</span>
                <span class="sr-only">Working…</span>
            @else
                {{ $slot }}
            @endif
        </button>
    @endif

    @if ($disabledReason)
        <span id="{{ $reasonId }}" class="t-body-sm text-ink-muted">{{ $disabledReason }}</span>
    @endif
</div>
