{{--
    A-09 — 403 Forbidden. FR-9.7, AC-F9.

    Shown when a Publisher reaches for member or payment data by direct URL.

    App Flow A-09: "Plain, no detail about what exists behind it, with a route
    back to their permitted dashboard." Naming the resource would tell somebody
    what they had found, which is half of what they were probing for.
--}}
<x-layouts::public title="Not available to your account">
    <div class="mx-auto max-w-prose px-4 sm:px-6 py-10 md:py-11">
        <h1 class="t-h1 text-forest-800">That page is not available to your account</h1>

        <p class="t-body-lg text-ink mt-5 measure-prose">
            Your account does not have access to this part of the site. If you
            think it should, the association can check your permissions.
        </p>

        <nav class="mt-8 flex flex-col gap-2" aria-label="Suggested pages">
            <a href="/" class="t-body text-forest-700 underline underline-offset-2">Home</a>
            <a href="/contact" class="t-body text-forest-700 underline underline-offset-2">Contact the association</a>
        </nav>
    </div>
</x-layouts::public>
