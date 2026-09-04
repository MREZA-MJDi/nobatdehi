@props([
'quantity' => 60,
'color' => null,
])

<canvas
    data-particles
    data-particles-quantity="{{ $quantity }}"
    @if ($color)
    data-particles-color="{{ $color }}"
    @endif
    aria-hidden="true"
    {{ $attributes->merge([
        'class' => 'pointer-events-none absolute inset-0 size-full',
    ]) }}
></canvas>
