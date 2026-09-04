@props([
'maxTilt' => 8,
])

<div
    data-tilt-card
    data-tilt-max="{{ $maxTilt }}"
    {{ $attributes->merge([
        'class' => 'will-change-transform [transform-style:preserve-3d]',
    ]) }}
>
    {{ $slot }}
</div>
