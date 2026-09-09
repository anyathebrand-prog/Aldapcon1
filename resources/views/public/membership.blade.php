{{--
    P-04 Membership overview — FR-1.4, FR-2.1, UI brief §7.

    A stacked comparison list, NOT a three-column pricing table (§7).

    "Pricing tables imply consumer tiers and a 'best value' nudge; a
    professional body has categories you qualify for, not plans you choose."

    Every fee uses tabular figures, because a ₦ figure should read as a fact
    (§1.2).
--}}
<x-layouts::public title="Membership"
                   description="Membership categories, eligibility and annual fees for the Association of Data Protection Compliance Organizations of Nigeria.">
    <div class="mx-auto max-w-app px-4 sm:px-6 py-8 md:py-10">
        <h1 class="t-h1 text-forest-800">Membership</h1>

        <p class="t-body-lg text-ink mt-5 measure-prose">
            Membership runs for twelve months from the day it is activated.
            Choose the category you qualify for — eligibility is stated against
            each one.
        </p>

        @if ($categories->isEmpty())
            {{--
                App Flow P-04: with no active category the Join calls to action
                are disabled with an explanation, and "this is a
                launch-blocking configuration error and should also alert an
                admin".

                It is not treated as a normal empty state, because it is not
                one: the association's primary conversion path is closed.
            --}}
            <div class="mt-8">
                <x-alert variant="warning" title="Membership opens shortly">
                    Categories and fees are being finalised. If you would like
                    to be told when membership opens, please get in touch.
                </x-alert>

                <div class="mt-6">
                    <x-button href="/contact" variant="secondary">Contact the association</x-button>
                </div>
            </div>
        @else
            <ul class="mt-9 divide-y divide-rule border-t border-rule">
                @foreach ($categories as $category)
                    <li class="py-8 flex flex-col gap-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-4">
                            <h2 class="t-h2 text-forest-800">{{ $category->name }}</h2>

                            {{-- §11 always-2 — tabular figures for money. --}}
                            <p class="t-h3 text-ink figure">
                                {{ $category->feeInNaira() }}
                                <span class="t-body-sm text-ink-muted font-normal">a year</span>
                            </p>
                        </div>

                        <p class="t-caption text-ink-muted">
                            {{ $category->applicant_type === 'organisation' ? 'For organisations' : 'For individuals' }}
                        </p>

                        <p class="t-body text-ink measure-prose">{{ $category->eligibility }}</p>

                        @if (! empty($category->benefits))
                            <ul class="flex flex-col gap-2 mt-1">
                                @foreach ($category->benefits as $benefit)
                                    <li class="t-body text-ink measure-ui flex gap-3">
                                        <span class="text-forest-700" aria-hidden="true">—</span>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($category->requires_verification)
                            {{--
                                Change set 01. Said here, before payment, not
                                discovered at the registration form.

                                An applicant who learns only after paying that
                                a human must approve them has been surprised at
                                the worst possible moment.
                            --}}
                            <p class="t-body-sm text-ink-muted measure-ui mt-1">
                                This category requires your NDPC licence number and a copy of
                                your certificate. Membership begins once an administrator has
                                reviewed it.
                            </p>
                        @endif

                        <div class="mt-3">
                            {{-- Routes to the payment flow in Phase 9a. --}}
                            <x-button href="/join" size="lg">
                                Join as {{ $category->name }}
                            </x-button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <p class="t-body-sm text-ink-muted mt-8 measure-ui">
                Not sure which applies to you?
                <a href="/faq" class="text-forest-700 underline underline-offset-2">Read the frequently asked questions</a>
                or <a href="/contact" class="text-forest-700 underline underline-offset-2">ask the association</a>.
            </p>
        @endif
    </div>
</x-layouts::public>
