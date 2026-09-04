@props([
'width' => 20,
'height' => 20,
'cx' => 1,
'cy' => 1,
'cr' => 1,
])

@php
    $patternId = 'dot-pattern-' . uniqid();
@endphp

<svg
    aria-hidden="true"
    data-slot="dot-pattern"
    {{ $attributes->merge([
        'class' => 'pointer-events-none absolute inset-0 size-full fill-muted-foreground/30',
    ]) }}
>
    <defs>
        <pattern
            id="{{ $patternId }}"
            width="{{ $width }}"
            height="{{ $height }}"
            patternUnits="userSpaceOnUse"
        >
            <circle
                cx="{{ $cx }}"
                cy="{{ $cy }}"
                r="{{ $cr }}"
            />
        </pattern>
    </defs>

    <rect
        width="100%"
        height="100%"
        stroke-width="0"
        fill="url(#{{ $patternId }})"
    />
</svg>
