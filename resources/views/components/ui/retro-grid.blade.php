@props([
'angle' => 55,
'cellSize' => 56,
'opacity' => 0.4,
])

<div
    aria-hidden="true"
    data-slot="retro-grid"
    {{ $attributes->merge([
        'class' => 'pointer-events-none absolute inset-0 overflow-hidden [perspective:240px]',
    ]) }}
    style="opacity: {{ $opacity }};"
>
    <div
        class="absolute inset-0"
        style="transform: rotateX({{ $angle }}deg);"
    >
        <div
            class="animate-retro-grid absolute h-[300vh] w-[600vw] [inset:0%_0px] [margin-left:-200%] [transform-origin:100%_0_0] [background-image:linear-gradient(to_right,var(--grid-line)_1px,transparent_0),linear-gradient(to_bottom,var(--grid-line)_1px,transparent_0)] [background-repeat:repeat]"
            style="
                background-size: {{ $cellSize }}px {{ $cellSize }}px;
                --grid-line: color-mix(in oklab, var(--border) 90%, transparent);
                "
        ></div>
    </div>

    <div
        class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-background"
    ></div>
</div>
