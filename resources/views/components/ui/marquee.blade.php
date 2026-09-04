@props([
'reverse' => false,
'pauseOnHover' => false,
'vertical' => false,
'repeat' => 4,
'fade' => true,
])

<div
    data-slot="marquee"
    @class([
        'group flex gap-[var(--gap)] overflow-hidden [--duration:40s] [--gap:1rem]',
        'flex-col' => $vertical,
        'flex-row' => !$vertical,

        '[mask-image:linear-gradient(to_bottom,transparent,black_12%,black_88%,transparent)]'
            => $fade && $vertical,

        '[mask-image:linear-gradient(to_right,transparent,black_12%,black_88%,transparent)]'
            => $fade && !$vertical,
    ])
    {{ $attributes }}
>
    @for ($i = 0; $i < $repeat; $i++)
        <div
            @class([
                'flex shrink-0 justify-around gap-[var(--gap)]',
                'animate-marquee-vertical flex-col' => $vertical,
                'animate-marquee flex-row' => !$vertical,
                '[animation-direction:reverse]' => $reverse,
                'group-hover:[animation-play-state:paused]' => $pauseOnHover,
            ])
            @if ($i > 0) aria-hidden="true" @endif
        >
            {{ $slot }}
        </div>
    @endfor
</div>
