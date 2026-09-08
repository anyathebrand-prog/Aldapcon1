{{--
    Public header — UI brief §5.3, App Flow G-03, G-05

    Desktop: forest-900 bar at 72px, wordmark left, links right, with a 3px
    flag-green rule beneath — the one place flag green appears at full
    strength.

    Mobile: 60px, wordmark and a disclosure. The panel slides from the right,
    full height, focus trapped, Esc closes, background scroll locked (§5.3).

    The right-hand action is context-aware (G-03): Join and Log in for
    visitors, My portal for members, Admin for staff. §11 never-17 —
    never show a member the Join call to action; an active member being urged
    to join is a credibility bug (App Flow P-01).

    Links here are plain paths, not route() calls. The public routes arrive in
    Phase 5; until then these 404, which is the correct behaviour for a shell
    and is visible rather than hidden behind a helper that would throw.
--}}

@props([
    'authenticated' => false,
    'portalUrl' => '/portal',
])

@php
    $nav = [
        'About' => '/about',
        'Leadership' => '/leadership',
        'Membership' => '/membership',
        'News' => '/news',
        'Events' => '/events',
        'FAQ' => '/faq',
        'Contact' => '/contact',
    ];
@endphp

<header x-data="{ open: false }" class="bg-forest-900">
    <div class="mx-auto max-w-app px-4 sm:px-6">
        <div class="flex items-center justify-between h-[60px] md:h-[72px]">
            <a href="/" class="flex items-center gap-2 text-white">
                <span class="t-h4 font-semibold">ALDAPCON</span>
                <span class="sr-only">— home</span>
            </a>

            {{-- Desktop navigation --}}
            <nav class="hidden md:flex items-center gap-6" aria-label="Primary">
                @foreach ($nav as $label => $href)
                    <a href="{{ $href }}"
                       class="t-body-sm text-white/90 hover:text-white border-b-2 border-transparent hover:border-flag pb-1 transition-colors duration-micro ease-enter">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden md:flex items-center gap-4">
                @if ($authenticated)
                    <a href="{{ $portalUrl }}" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">
                        My portal
                    </a>
                @else
                    <a href="/login" class="t-body-sm text-white/90 hover:text-white underline underline-offset-2">
                        Log in
                    </a>
                    <a href="/join"
                       class="inline-flex items-center justify-center min-h-control px-5 py-2 rounded-md t-body font-semibold bg-forest-700 text-white hover:bg-forest-800 transition-colors duration-micro ease-enter">
                        Join the association
                    </a>
                @endif
            </div>

            {{-- Mobile disclosure --}}
            <button type="button"
                    class="md:hidden inline-flex items-center justify-center min-h-control min-w-control text-white"
                    x-on:click="open = true"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-controls="mobile-menu">
                <span class="sr-only">Open menu</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- The one place flag green appears at full strength (§5.3). --}}
    <div class="h-[3px] bg-flag" aria-hidden="true"></div>

    {{-- Mobile panel. x-trap.noscroll traps focus while open, returns it to
         the trigger on close, and locks background scroll; Esc closes. All
         four are required by §5.3 and §10. --}}
    <div x-show="open"
         x-cloak
         id="mobile-menu"
         class="md:hidden fixed inset-0 z-40"
         role="dialog"
         aria-modal="true"
         aria-label="Menu">
        <div class="absolute inset-0 bg-forest-900/60" x-on:click="open = false" aria-hidden="true"></div>

        <div class="absolute right-0 top-0 h-full w-[86%] max-w-[360px] bg-forest-900 overflow-y-auto"
             x-trap.noscroll="open"
             x-on:keydown.escape.window="open = false">
            <div class="flex items-center justify-between h-[60px] px-4">
                <span class="t-h4 text-white font-semibold">Menu</span>
                <button type="button"
                        class="inline-flex items-center justify-center min-h-control min-w-control text-white"
                        x-on:click="open = false">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <nav class="flex flex-col px-4 pb-6" aria-label="Primary">
                @foreach ($nav as $label => $href)
                    <a href="{{ $href }}" class="t-body text-white/90 hover:text-white py-3 border-b border-white/10">
                        {{ $label }}
                    </a>
                @endforeach

                @if ($authenticated)
                    <a href="{{ $portalUrl }}" class="t-body text-white/90 hover:text-white py-3 border-b border-white/10">
                        My portal
                    </a>
                @else
                    <a href="/login" class="t-body text-white/90 hover:text-white py-3 border-b border-white/10">
                        Log in
                    </a>
                    {{-- Join is last, as a full-width primary (§5.3). --}}
                    <a href="/join"
                       class="mt-5 inline-flex items-center justify-center min-h-control-lg px-5 py-3 rounded-md t-body font-semibold bg-forest-700 text-white hover:bg-forest-800">
                        Join the association
                    </a>
                @endif
            </nav>
        </div>
    </div>
</header>
