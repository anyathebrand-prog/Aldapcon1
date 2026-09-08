{{--
    Checkbox — UI brief §5.2

    The ordinary checkbox. For consent, use <x-consent-checkbox>: NDPA
    requires unticked, unbundled opt-in with its own recorded text, and the
    distinction is deliberate rather than cosmetic (F-6).
--}}

@props([
    'name',
    'label',
    'checked' => false,
    'help' => null,
    'error' => null,
    'value' => '1',
])

@php
    $id = $attributes->get('id', $name);
    $helpId = $help ? $id.'-help' : null;
    $errorId = $error ? $id.'-error' : null;
    $describedBy = collect([$helpId, $errorId])->filter()->implode(' ');
@endphp

<div class="flex flex-col gap-1">
    <div class="flex items-start gap-3">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => implode(' ', [
                'mt-0.5 h-5 w-5 shrink-0 rounded-sm border-2 bg-white',
                'text-forest-700 focus:ring-0 focus:ring-offset-0',
                $error ? 'border-error-border' : 'border-border',
            ])]) }}
        />
        <label for="{{ $id }}" class="t-body text-ink measure-ui cursor-pointer">{{ $label }}</label>
    </div>

    @if ($help)
        <p id="{{ $helpId }}" class="t-body-sm text-ink-muted pl-8 measure-ui">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $errorId }}" class="t-body-sm text-error-fg pl-8">{{ $error }}</p>
    @endif
</div>
