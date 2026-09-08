{{--
    Select — UI brief §5.2

    Shares the input's label, help and error contract. A native <select> is
    used deliberately: on a mid-range Android it opens the OS picker, which is
    faster, accessible for free, and adds no JavaScript to the critical path.
--}}

@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $helpId = $help ? $id.'-help' : null;
    $errorId = $error ? $id.'-error' : null;
    $describedBy = collect([$helpId, $errorId])->filter()->implode(' ');
@endphp

<div class="flex flex-col gap-2">
    <label for="{{ $id }}" class="t-caption text-ink">{{ $label }}</label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required @endif
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->merge(['class' => implode(' ', [
            'w-full h-[48px] px-3 rounded-sm t-body bg-white text-ink',
            'border transition-colors duration-micro ease-enter focus:border-forest-700',
            $error ? 'border-error-border' : 'border-border',
        ])]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $selected)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($help)
        <p id="{{ $helpId }}" class="t-body-sm text-ink-muted measure-ui">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $errorId }}" class="t-body-sm text-error-fg">{{ $error }}</p>
    @endif
</div>
