{{--
    A-07 — Two-factor enrolment. Derived screen (App Flow G-4, plan C-4).

    FR-4.3 makes 2FA mandatory for Super Admin and Admin but specifies no
    enrolment step. Without this screen the first admin login cannot complete:
    they have no second factor, the challenge cannot be answered, and there is
    nowhere to create one.

    App Flow A-07 requires four things here, and all four are present:
    a QR code, a manual key for anyone who cannot scan, a verification field,
    and recovery codes that must be acknowledged as saved before proceeding.
--}}
<x-layouts::form title="Set up two-factor authentication">
    <h1 class="t-h1 text-forest-800">Set up two-factor authentication</h1>

    @if (session('status'))
        <div class="mt-5"><x-alert variant="info">{{ session('status') }}</x-alert></div>
    @endif

    @if ($enrolled)
        <div class="mt-5">
            <x-alert variant="success" title="Two-factor authentication is on">
                Your account is protected. You will be asked for a code each time you log in.
            </x-alert>
        </div>

        <div class="mt-7">
            <x-button href="/admin" size="lg">Go to the dashboard</x-button>
        </div>
    @else
        <p class="t-body text-ink mt-5 measure-ui">
            Administrator accounts can reach member records and payment data, so a
            second factor is required before you can continue. You will need an
            authenticator app such as Google Authenticator, 1Password or Authy.
        </p>

        @if (! $hasSecret)
            <form method="POST" action="/user/two-factor-authentication" class="mt-7">
                @csrf
                <x-button type="submit" size="lg">Begin setup</x-button>
            </form>
        @else
            <div class="mt-8 flex flex-col gap-8">
                <section class="flex flex-col gap-3">
                    <h2 class="t-h3 text-forest-800">1. Scan this code</h2>
                    <div class="bg-white border border-border rounded-sm p-5 inline-block">
                        {!! request()->user()->twoFactorQrCodeSvg() !!}
                    </div>

                    {{-- A-07 requires a manual key. Scanning fails on a desktop
                         with no camera, and on a phone whose camera the person
                         cannot use. --}}
                    <details class="mt-1">
                        <summary class="t-body text-forest-700 underline underline-offset-2 cursor-pointer min-h-control flex items-center">
                            I cannot scan the code
                        </summary>
                        <p class="t-body-sm text-ink-muted mt-3">Enter this key into your app by hand:</p>
                        <p class="t-body figure mt-2 break-all bg-surface border border-rule p-3 rounded-sm">
                            {{ decrypt(request()->user()->two_factor_secret) }}
                        </p>
                    </details>
                </section>

                <section class="flex flex-col gap-3">
                    <h2 class="t-h3 text-forest-800">2. Save your recovery codes</h2>
                    <p class="t-body text-ink measure-ui">
                        Each code works once. They are the only way back into your
                        account if you lose your phone — store them somewhere other
                        than the phone itself.
                    </p>

                    <ul class="bg-surface border border-rule rounded-sm p-5 flex flex-col gap-2">
                        @foreach (json_decode(decrypt(request()->user()->two_factor_recovery_codes), true) as $code)
                            <li class="t-body figure">{{ $code }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="flex flex-col gap-3">
                    <h2 class="t-h3 text-forest-800">3. Confirm it works</h2>

                    <form method="POST" action="/user/confirmed-two-factor-authentication"
                          class="flex flex-col gap-5">
                        @csrf

                        <x-input name="code" label="Six-digit code from your app"
                                 inputmode="numeric" autocomplete="one-time-code"
                                 placeholder="123456"
                                 :error="$errors->first('code')" />

                        {{-- The acknowledgement is not ceremony. Recovery codes
                             are the only route back for an admin who loses their
                             phone, and an association with one to three staff has
                             nobody else who can restore that access. --}}
                        <x-checkbox name="saved_codes" required
                                    label="I have saved my recovery codes somewhere safe" />

                        <div class="pt-1">
                            <x-button type="submit" size="lg">Turn on two-factor authentication</x-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    @endif
</x-layouts::form>
