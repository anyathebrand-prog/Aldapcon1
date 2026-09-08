{{--
    Empty state — UI brief §5.6, §11 always-9

    Left-aligned. No illustration, no icon. A heading, one sentence explaining
    what would be here, and a primary action if one exists.

    Where there is nothing to do, the SECTION IS HIDDEN ENTIRELY rather than
    shown empty — that decision belongs to the calling page, not to this
    component. An empty slot reads as neglect (App Flow P-01).

    Plan §2 rule 9: every list view ships its empty state in the same commit as
    the list. This component exists so there is no excuse not to.
--}}

@props([
    'heading',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-start gap-3 py-8']) }}>
    <p class="t-h4 text-ink">{{ $heading }}</p>
    <p class="t-body text-ink-muted measure-ui">{{ $slot }}</p>

    @isset($action)
        <div class="pt-1">{{ $action }}</div>
    @endisset
</div>
