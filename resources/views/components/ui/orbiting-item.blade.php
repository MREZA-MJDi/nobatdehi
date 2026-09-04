@props([
'index' => 0,
'count' => 1,
'iconSize' => null,
'reverse' => false,
])

@php
    $angle = $count > 0
        ? (360 / $count) * $index
        : 0;

    $size = $iconSize ?? 'var(--orbit-icon-size)';
@endphp

<div
    data-slot="orbiting-item"
    @class([
        'animate-orbit absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 transform-gpu items-center justify-center rounded-full',
        '[animation-direction:reverse]' => $reverse,
    ])
    style="
        --orbit-angle: {{ $angle }}deg;
        --orbit-item-size: {{ is_numeric($size) ? $size . 'px' : $size }};
        "
>
    <div
        class="flex items-center justify-center"
        style="
            width: var(--orbit-item-size);
            height: var(--orbit-item-size);
        "
    >
        {{ $slot }}
    </div>
</div>
