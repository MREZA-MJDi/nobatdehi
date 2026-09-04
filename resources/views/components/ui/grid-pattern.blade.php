@props([
'width' => 40,
'height' => 40,
'x' => -1,
'y' => -1,
'squares' => null,
'strokeDasharray' => '0',
])

@php
    $patternId = 'grid-pattern-' . uniqid();
@endphp

<svg
    aria-hidden="true"
    data-slot="grid-pattern"
    {{ $attributes->merge([
        'class' => 'pointer-events-none absolute inset-0 size-full fill-muted/40 stroke-border',
    ]) }}
>
    <defs>
        <pattern
            id="{{ $patternId }}"
            width="{{ $width }}"
            height="{{ $height }}"
            patternUnits="userSpaceOnUse"
            x="{{ $x }}"
            y="{{ $y }}"
        >
            <path
                d="M.5 {{ $height }}V.5H{{ $width }}"
                fill="none"
                stroke-dasharray="{{ $strokeDasharray }}"
            />
        </pattern>
    </defs>

    <rect
        width="100%"
        height="100%"
        stroke-width="0"
        fill="url(#{{ $patternId }})"
    />

    @if ($squares)
        <svg
            x="{{ $x }}"
            y="{{ $y }}"
            class="overflow-visible"
        >
            @foreach ($squares as $square)
                @php
                    $sx = $square[0] ?? 0;
                    $sy = $square[1] ?? 0;
                @endphp

                <rect
                    x="{{ ($sx * $width) + 1 }}"
                    y="{{ ($sy * $height) + 1 }}"
                    width="{{ $width - 1 }}"
                    height="{{ $height - 1 }}"
                    stroke-width="0"
                />
            @endforeach
        </svg>
    @endif
</svg>
