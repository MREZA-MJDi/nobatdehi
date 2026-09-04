@props([
'label' => null,
])

<div
    data-dock-icon
    @if ($label)
    aria-label="{{ $label }}"
    title="{{ $label }}"
    @endif
    {{ $attributes->merge([
        'class' => 'flex aspect-square cursor-pointer items-center justify-center rounded-full bg-muted text-muted-foreground transition-colors hover:text-foreground',
        'style' => 'width: 40px; height: 40px;',
    ]) }}
>
    {{ $slot }}
</div>
