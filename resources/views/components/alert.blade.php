{{--
    Inline alert — UI brief §5.6

    Full-width bar, 4px left border in the semantic colour, tinted background,
    square corners, icon plus text.

    It sits directly above the content it concerns, never floated at the top of
    the page — an alert about a field the user cannot see is not feedback.

    Not a toast. §5.6 and §11 never-12 reserve toasts for reversible,
    non-critical confirmations; payment, membership and destructive outcomes
    get a persistent on-screen state, because a toast the user missed is a
    support ticket.
--}}

@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $styles = [
        'success' => 'bg-success-bg text-success-fg border-l-success-border',
        'warning' => 'bg-warning-bg text-warning-fg border-l-warning-border',
        'error' => 'bg-error-bg text-error-fg border-l-error-border',
        'info' => 'bg-info-bg text-info-fg border-l-info-border',
    ];

    $key = array_key_exists($variant, $styles) ? $variant : 'info';

    // Errors are announced assertively; everything else politely. An error the
    // screen reader mentions after the user has moved on is not an error
    // message (§10).
    $role = $key === 'error' ? 'alert' : 'status';
@endphp

<div role="{{ $role }}"
     {{ $attributes->merge(['class' => 'flex items-start gap-3 border-l-4 rounded-none px-4 py-3 '.$styles[$key]]) }}>
    <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        @if ($key === 'success')
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
            <path d="m6.5 10 2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        @elseif ($key === 'warning')
            <path d="M10 3 2.5 16.5h15L10 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            <path d="M10 8v3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="10" cy="14" r="0.9" fill="currentColor"/>
        @elseif ($key === 'error')
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
            <path d="M10 6v4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="10" cy="13.5" r="0.9" fill="currentColor"/>
        @else
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
            <path d="M10 9.5v4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="10" cy="6.5" r="0.9" fill="currentColor"/>
        @endif
    </svg>

    <div class="flex flex-col gap-1 measure-ui">
        @if ($title)
            <p class="t-h4">{{ $title }}</p>
        @endif
        <div class="t-body">{{ $slot }}</div>
    </div>
</div>
