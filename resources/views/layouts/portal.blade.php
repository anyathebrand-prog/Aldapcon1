{{--
    Portal layout — UI brief §5.3, §8

    Horizontal tabs on desktop under a compact member bar; a four-item bottom
    tab bar on mobile.

    The four are Dashboard, Payments, Events, More (audit IG-6). Payment and
    renewal actions live in thumb reach; profile editing is low-frequency and
    does not earn a slot, so Profile, Announcements and Security sit in More.

    The bottom bar is portal-only. §8 keeps a top disclosure menu on the public
    site, because a bottom bar on marketing pages competes with browser chrome.
--}}

@props([
    'title' => null,
    'current' => 'dashboard',
])

@php
    $mobileTabs = [
        'dashboard' => ['label' => 'Dashboard', 'href' => '/portal'],
        'payments' => ['label' => 'Payments', 'href' => '/portal/payments'],
        'events' => ['label' => 'Events', 'href' => '/portal/events'],
        'more' => ['label' => 'More', 'href' => '/portal/more'],
    ];

    $desktopTabs = [
        'dashboard' => ['label' => 'Dashboard', 'href' => '/portal'],
        'profile' => ['label' => 'Profile', 'href' => '/portal/profile'],
        'payments' => ['label' => 'Payments', 'href' => '/portal/payments'],
        'events' => ['label' => 'Events', 'href' => '/portal/events'],
        'announcements' => ['label' => 'Announcements', 'href' => '/portal/announcements'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title ? $title.' — ALDAPCON' : 'My portal — ALDAPCON' }}</title>

    <link rel="preload" href="/fonts/archivo-latin-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/literata-latin-var.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-paper text-ink">
    <a href="#main" class="skip-link t-body">Skip to content</a>

    <x-site-header :authenticated="true" />

    {{-- Desktop tabs --}}
    <nav class="hidden md:block bg-surface border-b border-rule" aria-label="Portal">
        <div class="mx-auto max-w-app px-4 sm:px-6">
            <ul class="flex items-center gap-6">
                @foreach ($desktopTabs as $key => $tab)
                    <li>
                        <a href="{{ $tab['href'] }}"
                           @if ($current === $key) aria-current="page" @endif
                           class="inline-flex items-center min-h-control t-body-sm border-b-2 -mb-px {{ $current === $key ? 'border-forest-700 text-forest-800 font-semibold' : 'border-transparent text-ink-muted hover:text-ink' }}">
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- Bottom padding keeps the sticky bar from covering the last element
         (§8): the form gets padding equal to the bar height. --}}
    <main id="main" class="flex-1 w-full pb-[76px] md:pb-0">
        <div class="mx-auto max-w-app px-4 sm:px-6 py-6 md:py-8">
            {{ $slot }}
        </div>
    </main>

    {{-- Mobile bottom tabs --}}
    <nav class="md:hidden fixed inset-x-0 bottom-0 z-20 bg-paper border-t border-rule shadow-sticky" aria-label="Portal">
        <ul class="grid grid-cols-4">
            @foreach ($mobileTabs as $key => $tab)
                <li>
                    <a href="{{ $tab['href'] }}"
                       @if ($current === $key) aria-current="page" @endif
                       class="flex items-center justify-center min-h-control-lg t-body-sm {{ $current === $key ? 'text-forest-800 font-semibold' : 'text-ink-muted' }}">
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</body>
</html>
