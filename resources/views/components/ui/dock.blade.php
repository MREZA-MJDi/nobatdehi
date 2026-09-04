@props([
'baseSize' => 40,
'magnification' => 64,
'distance' => 140,
])

<div
    data-dock
    data-dock-base-size="{{ $baseSize }}"
    data-dock-magnification="{{ $magnification }}"
    data-dock-distance="{{ $distance }}"
    {{ $attributes->merge([
        'class' => 'mx-auto flex h-16 w-fit items-end gap-2 rounded-2xl border bg-card/70 px-3 pb-2 backdrop-blur-xl',
    ]) }}
>
    {{ $slot }}
</div>
