@props([
'duration' => 20,
'radius' => 160,
'reverse' => false,
'path' => true,
'iconSize' => 32,
'speed' => 1,
])

@php
    $calculatedDuration = $speed > 0
        ? $duration / $speed
        : $duration;
@endphp

<div
    data-slot="orbiting-circles"
    {{ $attributes->merge([
        'class' => 'absolute inset-0',
    ]) }}
    style="
        --orbit-duration: {{ $calculatedDuration }}s;
        --orbit-radius: {{ $radius }}px;
        --orbit-icon-size: {{ $iconSize }}px;
        "
>
    @if ($path)
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 flex items-center justify-center"
        >
            <div
                class="rounded-full border border-dashed border-border"
                style="
                    width: {{ $radius * 2 }}px;
                    height: {{ $radius * 2 }}px;
                    "
            ></div>
        </div>
    @endif

    {{ $slot }}
</div>
