{{--
    Public layout — UI brief §7, §10; PRD FR-1.5, NFR 6.7

    Server-rendered, per TRD §2.1. No hydration, no rendering-mode decision,
    and the SEO requirement satisfied by default.

    The skip link is the FIRST focusable element on every page (§10). Fonts are
    preloaded because they sit on the critical path for LCP (TRD §7.1) — the
    hero is typographic, so a late font is a late largest-contentful paint.
--}}

@props([
    'title' => null,
    'description' => null,
    'authenticated' => false,
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ? $title.' — ALDAPCON' : 'ALDAPCON — Association of Data Protection Compliance Organizations of Nigeria' }}</title>

    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    {{-- Self-hosted, so preload rather than preconnect to a third party
         (TRD §7.1). crossorigin is required for fonts even same-origin. --}}
    <link rel="preload" href="/fonts/archivo-latin-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/literata-latin-var.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-paper text-ink">
    <a href="#main" class="skip-link t-body">Skip to content</a>

    <x-site-header :authenticated="$authenticated" />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-site-footer />
    <x-cookie-banner />
</body>
</html>
