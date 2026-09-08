{{-- Password confirmation. Sits in front of enrolling or disabling a second
     factor, so a borrowed session cannot silently remove somebody's 2FA. --}}
<x-layouts::form title="Confirm your password">
    <h1 class="t-h1 text-forest-800">Confirm your password</h1>

    <p class="t-body text-ink mt-5 measure-ui">
        For your security, please confirm your password before continuing.
    </p>

    <form method="POST" action="/user/confirm-password" class="mt-7 flex flex-col gap-5">
        @csrf

        <x-input name="password" label="Password" type="password"
                 required autocomplete="current-password" autofocus
                 :error="$errors->first('password')" />

        <div class="pt-1">
            <x-button type="submit" size="lg">Confirm</x-button>
        </div>
    </form>
</x-layouts::form>
