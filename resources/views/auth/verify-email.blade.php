{{-- A-04 Email verification notice — FR-4.2 --}}
<x-layouts::form title="Verify your email address">
    <h1 class="t-h1 text-forest-800">Verify your email address</h1>

    <p class="t-body text-ink mt-5 measure-ui">
        We have sent a link to your email address. Open it to confirm the
        address is yours. If it has not arrived in a few minutes, check your
        spam folder.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-5">
            <x-alert variant="success">A new link is on its way.</x-alert>
        </div>
    @endif

    <form method="POST" action="/email/verification-notification" class="mt-7">
        @csrf
        {{-- Rate-limited by Fortify (App Flow A-04). --}}
        <x-button type="submit" variant="secondary">Send the link again</x-button>
    </form>
</x-layouts::form>
