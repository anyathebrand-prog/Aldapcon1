{{-- A-03 Reset password — FR-4.2. Single-use, expiring link. --}}
<x-layouts::form title="Choose a new password">
    <h1 class="t-h1 text-forest-800">Choose a new password</h1>

    {{-- Rules stated up front, never revealed by rejection
         (UI brief §11 always-14, App Flow J-06 edge case). --}}
    <p class="t-body text-ink mt-5 measure-ui">
        Use at least 12 characters. A phrase you can remember is stronger than
        a short password with symbols in it.
    </p>

    <form method="POST" action="/reset-password" class="mt-7 flex flex-col gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-input name="email" label="Email address" type="email"
                 :value="old('email', $request->email)" required
                 autocomplete="email" :error="$errors->first('email')" />

        <x-input name="password" label="New password" type="password"
                 required autocomplete="new-password"
                 :error="$errors->first('password')" />

        <x-input name="password_confirmation" label="Confirm new password" type="password"
                 required autocomplete="new-password" />

        <div class="pt-1">
            <x-button type="submit" size="lg">Save my new password</x-button>
        </div>
    </form>
</x-layouts::form>
