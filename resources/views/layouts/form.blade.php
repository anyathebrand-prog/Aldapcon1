{{--
    Form layout — UI brief §4.3, §7

    container-form at 560px, single column.

    Narrow on purpose: it keeps the registration form (J-06, the longest in the
    product) from sprawling into a two-column layout that people fill in the
    wrong order.

    The wordmark is retained in the header throughout the paid journey (§7).
    Payment flows that strip branding lose trust exactly when they need it
    most.
--}}

@props([
    'title' => null,
    'authenticated' => false,
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Signup and portal pages are never indexed. --}}
    <meta name="robots" content="noindex">
    <title>{{ $title ? $title.' — ALDAPCON' : 'ALDAPCON' }}</title>

    <link rel="preload" href="/fonts/archivo-latin-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/literata-latin-var.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-paper text-ink">
    <a href="#main" class="skip-link t-body">Skip to content</a>

    <x-site-header :authenticated="$authenticated" />

    <main id="main" class="flex-1 w-full">
        <div class="mx-auto max-w-form px-4 sm:px-6 py-8 md:py-10">
            {{ $slot }}
        </div>
    </main>

    <x-site-footer />
</body>
</html>
