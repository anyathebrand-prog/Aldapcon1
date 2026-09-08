{{-- A-02 Forgot password — FR-4.2 --}}
<x-layouts::form title="Reset your password">
    <h1 class="t-h1 text-forest-800">Reset your password</h1>

    <p class="t-body text-ink mt-5 measure-ui">
        Enter the email address on your membership and we will send you a link
        to set a new password.
    </p>

    {{-- Neutral confirmation regardless of whether the account exists
         (App Flow A-02). The same words either way, or this becomes an
         oracle for which addresses are registered. --}}
    @if (session('status'))
        <div class="mt-5"><x-alert variant="success">{{ session('status') }}</x-alert></div>
    @endif

    <form method="POST" action="/forgot-password" class="mt-7 flex flex-col gap-5">
        @csrf

        <x-input name="email" label="Email address" type="email"
                 :value="old('email')" required autocomplete="email" autofocus
                 :error="$errors->first('email')" />

        <div class="pt-1">
            <x-button type="submit" size="lg">Send me a reset link</x-button>
        </div>

        <a href="/login" class="t-body text-forest-700 underline underline-offset-2">Back to log in</a>
    </form>
</x-layouts::form>
