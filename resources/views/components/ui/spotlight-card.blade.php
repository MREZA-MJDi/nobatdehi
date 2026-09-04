@props([
'radius' => 320,
'color' => 'color-mix(in oklab, var(--brand, #8b5cf6) 14%, transparent)',
])

<div
    data-spotlight-card
    data-spotlight-radius="{{ $radius }}"
    data-spotlight-color="{{ $color }}"
    {{ $attributes->merge([
        'class' => 'group relative overflow-hidden rounded-2xl border bg-card',
    ]) }}
>
    {{-- Spotlight glow --}}
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100 motion-reduce:transition-none"
        style="
            background:
                radial-gradient(
                    var(--spot-radius, 320px) circle at
                    var(--spot-x, 50%)
                    var(--spot-y, 50%),
                    var(--spot-color),
                    transparent 65%
                );
        "
    ></div>

    {{-- Content --}}
    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
