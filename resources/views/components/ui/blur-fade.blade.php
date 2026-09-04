@props([
'delay' => 0,
'duration' => 0.5,
'direction' => 'up',
'offset' => 16,
'once' => true,
])

@php
    $directions = [
        'up' => ['x' => 0, 'y' => $offset],
        'down' => ['x' => 0, 'y' => -$offset],
        'left' => ['x' => $offset, 'y' => 0],
        'right' => ['x' => -$offset, 'y' => 0],
        'none' => ['x' => 0, 'y' => 0],
    ];

    $move = $directions[$direction] ?? $directions['up'];
@endphp

<div
    data-blur-fade
    data-blur-fade-once="{{ $once ? 'true' : 'false' }}"
    data-blur-fade-delay="{{ $delay }}"
    data-blur-fade-duration="{{ $duration }}"
    style="
        --blur-fade-x: {{ $move['x'] }}px;
        --blur-fade-y: {{ $move['y'] }}px;
        opacity: 0;
        filter: blur(6px);
        transform: translate3d(
        var(--blur-fade-x),
        var(--blur-fade-y),
        0
        );
        "
    {{ $attributes }}
>
    {{ $slot }}
</div>
