{{--
    File upload — UI brief §5.2, F-15; change set 01

    The only file input in the member-facing product, and therefore the only
    path by which an outside party can place a file on the server (TRD §11.7).
    This component is the presentation half; validation, MIME allow-listing,
    re-encoding, hashing and private storage arrive in Phase 9b and are
    hardened again in Phase 16. Nothing here should ever be mistaken for a
    security control.

    Two rules the brief is emphatic about:

    ACCEPTED FORMATS AND THE SIZE LIMIT ARE STATED ABOVE THE CONTROL, not only
    in the error afterwards (§5.2, §11 always-15). Discovering an 8 MB cap by
    breaching it, after paying, is a bad moment in an already anxious flow.

    A DRAG-ONLY ZONE IS NOT OPERABLE. There is a real <button> inside for
    keyboard and screen-reader users; dragging is an enhancement on top of it.

    States: idle, dragging, selected, uploading, complete, error.

    Under prefers-reduced-motion the determinate bar becomes a static
    percentage with a polite aria-live update. An upload with no visible
    progress on Nigerian 4G reads as a frozen page — and the applicant has
    already paid (F-15). The reduced-motion path must carry identical
    information, never less.
--}}

@props([
    'name',
    'label',
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'acceptLabel' => 'PDF, JPG or PNG',
    'maxSizeLabel' => '8 MB',
    'help' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $limitsId = $id.'-limits';
    $helpId = $help ? $id.'-help' : null;
    $errorId = $error ? $id.'-error' : null;
    $describedBy = collect([$limitsId, $helpId, $errorId])->filter()->implode(' ');
@endphp

<div
    class="flex flex-col gap-2"
    x-data="{
        state: '{{ $error ? 'error' : 'idle' }}',
        fileName: '',
        fileSize: '',
        progress: 0,
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },
        select(file) {
            if (! file) return;
            this.fileName = file.name;
            this.fileSize = this.formatSize(file.size);
            this.state = 'selected';
        },
        clear() {
            this.state = 'idle';
            this.fileName = '';
            this.fileSize = '';
            this.progress = 0;
            $refs.input.value = '';
        },
    }"
>
    <span class="t-caption text-ink">{{ $label }}</span>

    {{-- Limits ABOVE the control. This is the point of the component. --}}
    <p id="{{ $limitsId }}" class="t-body-sm text-ink-muted">
        {{ $acceptLabel }}. Maximum {{ $maxSizeLabel }}.
    </p>

    @if ($help)
        <p id="{{ $helpId }}" class="t-body-sm text-ink-muted measure-ui">{{ $help }}</p>
    @endif

    <div
        x-on:dragover.prevent="state === 'idle' || state === 'error' ? state = 'dragging' : null"
        x-on:dragleave.prevent="state === 'dragging' ? state = 'idle' : null"
        x-on:drop.prevent="
            $refs.input.files = $event.dataTransfer.files;
            select($event.dataTransfer.files[0]);
        "
        class="rounded-sm border p-5 transition-colors duration-micro ease-enter"
        :class="{
            'border-border bg-white': state === 'idle' || state === 'selected' || state === 'uploading',
            'border-forest-700 bg-forest-100': state === 'dragging',
            'border-success-border bg-success-bg': state === 'complete',
            'border-error-border bg-error-bg': state === 'error',
        }"
    >
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            x-ref="input"
            x-on:change="select($event.target.files[0])"
            @if ($required) required @endif
            @if ($error) aria-invalid="true" @endif
            aria-describedby="{{ $describedBy }}"
            class="sr-only"
        />

        {{-- Idle and dragging --}}
        <div x-show="state === 'idle' || state === 'dragging'" class="flex flex-col items-start gap-3">
            <p class="t-body text-ink-muted" x-text="state === 'dragging' ? 'Drop the file to attach it' : 'Drag a file here, or choose one.'"></p>
            <button type="button"
                    x-on:click="$refs.input.click()"
                    class="inline-flex items-center justify-center min-h-control px-5 py-3 rounded-md t-body font-semibold bg-transparent text-forest-800 border border-border hover:bg-forest-50 transition-colors duration-micro ease-enter">
                Choose a file
            </button>
        </div>

        {{-- Selected --}}
        <div x-show="state === 'selected'" x-cloak class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-col">
                <span class="t-body text-ink" x-text="fileName"></span>
                <span class="t-body-sm text-ink-muted figure" x-text="fileSize"></span>
            </div>
            <button type="button" x-on:click="clear()"
                    class="t-body text-forest-700 underline underline-offset-2 min-h-control px-2">
                Remove
            </button>
        </div>

        {{-- Uploading. Determinate, because an indeterminate bar tells a
             person on 4G nothing about whether to keep waiting. --}}
        <div x-show="state === 'uploading'" x-cloak class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="t-body text-ink" x-text="fileName"></span>
                <span class="t-body-sm text-ink-muted figure" x-text="progress + '%'"></span>
            </div>

            {{-- The bar is hidden under reduced motion; the percentage above
                 and the live region below carry the same information. --}}
            <div class="h-2 w-full bg-surface rounded-sm overflow-hidden motion-reduce:hidden">
                <div class="h-full bg-forest-700 transition-all duration-standard ease-enter"
                     :style="`width: ${progress}%`"></div>
            </div>

            <p class="sr-only motion-reduce:not-sr-only motion-reduce:t-body-sm motion-reduce:text-ink-muted"
               aria-live="polite"
               x-text="`Uploading — ${progress}% complete`"></p>
        </div>

        {{-- Complete --}}
        <div x-show="state === 'complete'" x-cloak class="flex items-center gap-3">
            <svg class="h-5 w-5 shrink-0 text-success-fg" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                <path d="m6.5 10 2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="flex flex-col">
                <span class="t-body text-success-fg">Uploaded</span>
                <span class="t-body-sm text-ink-muted" x-text="fileName"></span>
            </div>
        </div>
    </div>

    @if ($error)
        <p id="{{ $errorId }}" class="t-body-sm text-error-fg flex items-start gap-2">
            <svg class="h-4 w-4 shrink-0 mt-0.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 5v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="8" cy="11" r="0.75" fill="currentColor"/>
            </svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
