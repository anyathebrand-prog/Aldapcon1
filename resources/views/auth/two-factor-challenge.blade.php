{{-- A-06 Two-factor challenge — FR-4.3.
     This screen sits between valid credentials and any admin data. --}}
<x-layouts::form title="Two-factor authentication">
    <h1 class="t-h1 text-forest-800">Enter your authentication code</h1>

    @if ($errors->any())
        <div class="mt-5"><x-alert variant="error">{{ $errors->first() }}</x-alert></div>
    @endif

    <div x-data="{ recovery: false }" class="mt-7 flex flex-col gap-5">
        <form method="POST" action="/two-factor-challenge" class="flex flex-col gap-5">
            @csrf

            <div x-show="! recovery">
                <x-input name="code" label="Six-digit code"
                         inputmode="numeric" autocomplete="one-time-code"
                         placeholder="123456"
                         help="From your authenticator app." />
            </div>

            {{-- A recovery code is the only way back for somebody who has lost
                 their phone. With one to three staff there is nobody else who
                 can restore that access. --}}
            <div x-show="recovery" x-cloak>
                <x-input name="recovery_code" label="Recovery code"
                         autocomplete="one-time-code"
                         help="One of the codes you saved when you set this up. Each works once." />
            </div>

            <div class="pt-1">
                <x-button type="submit" size="lg">Verify and continue</x-button>
            </div>
        </form>

        <button type="button" x-on:click="recovery = ! recovery"
                class="t-body text-forest-700 underline underline-offset-2 text-left min-h-control">
            <span x-show="! recovery">Use a recovery code instead</span>
            <span x-show="recovery" x-cloak>Use my authenticator app instead</span>
        </button>
    </div>
</x-layouts::form>
