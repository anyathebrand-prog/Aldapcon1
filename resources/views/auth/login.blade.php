{{-- A-01 Login — FR-4.1, FR-4.2, UI brief §7 --}}
<x-layouts::form title="Log in">
    <h1 class="t-h1 text-forest-800">Log in</h1>

    @if (session('status'))
        <div class="mt-5"><x-alert variant="info">{{ session('status') }}</x-alert></div>
    @endif

    {{-- AC-F4 — one generic message for wrong password, unknown email and
         deactivated account alike. Anything more specific tells an attacker
         which emails are registered. --}}
    @if ($errors->any())
        <div class="mt-5">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    <form method="POST" action="/login" class="mt-7 flex flex-col gap-5">
        @csrf

        <x-input name="email" label="Email address" type="email"
                 :value="old('email')" required
                 autocomplete="email" autofocus />

        <x-input name="password" label="Password" type="password"
                 required autocomplete="current-password" />

        <x-checkbox name="remember" label="Keep me logged in on this device" />

        <div class="pt-1">
            <x-button type="submit" size="lg">Log in</x-button>
        </div>

        <a href="/forgot-password" class="t-body text-forest-700 underline underline-offset-2">
            I have forgotten my password
        </a>
    </form>

    <hr class="border-0 border-t border-rule my-8" />

    <p class="t-body text-ink">
        Not a member yet?
        <a href="/join" class="text-forest-700 underline underline-offset-2">Join the association</a>.
    </p>
</x-layouts::form>
