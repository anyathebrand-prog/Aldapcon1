{{--
    Status badge — UI brief §2.4, F-3

    Membership status is the most important information in the product, and
    every badge carries THREE simultaneous signals: a text label, a shape, and
    colour.

    The shape is not decoration. Green/amber — Active versus Expiring soon — is
    the classic deuteranopia confusion pair, and those two states are the ones
    that drive renewal revenue. Colour alone would be a real failure for
    roughly one in twelve male users (F-3), so the dot differs per status:

        active     filled disc
        expiring   half-filled disc
        expired    hollow ring
        suspended  hollow ring with a bar
        pending    dotted outline

    Two deliberate decisions live here:

    "Expired" is slate, NOT red (§2.4). An expired member is the highest-value
    renewal prospect in the product, and the journey turns on making them feel
    lapsed rather than rejected. Red says "you failed"; slate says "this needs
    renewing". The renewal call to action beside it stays a full-strength
    primary button.

    "In review" gets a fifth hue rather than reusing the expired slate
    (§11 never-20). Two neutral statuses meaning opposite things — one is "we
    are working on it", the other is "you have lapsed" — is a legibility
    failure that colour-blind users would feel worst, sitting next to the most
    sensitive moment in the product.
--}}

@props([
    'status' => 'active',
    'label' => null,
])

@php
    $styles = [
        'active' => 'bg-status-active-bg text-status-active-fg',
        'expiring' => 'bg-status-expiring-bg text-status-expiring-fg',
        'expired' => 'bg-status-expired-bg text-status-expired-fg',
        'suspended' => 'bg-status-suspended-bg text-status-suspended-fg',
        'pending' => 'bg-status-pending-bg text-status-pending-fg',
    ];

    $labels = [
        'active' => 'Active',
        'expiring' => 'Expiring soon',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'pending' => 'In review',
    ];

    $key = array_key_exists($status, $styles) ? $status : 'active';
    $text = $label ?? $labels[$key];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-2 py-1 rounded-sm t-micro '.$styles[$key]]) }}>
    <svg class="h-2.5 w-2.5 shrink-0" viewBox="0 0 10 10" aria-hidden="true">
        @switch($key)
            @case('active')
                <circle cx="5" cy="5" r="4" fill="currentColor"/>
                @break
            @case('expiring')
                {{-- Half-filled: the ring plus a filled half. --}}
                <circle cx="5" cy="5" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                <path d="M5 1a4 4 0 0 1 0 8Z" fill="currentColor"/>
                @break
            @case('expired')
                <circle cx="5" cy="5" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                @break
            @case('suspended')
                <circle cx="5" cy="5" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                <path d="M2.5 5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                @break
            @case('pending')
                <circle cx="5" cy="5" r="4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="1.8 1.6"/>
                @break
        @endswitch
    </svg>
    {{ $text }}
</span>
