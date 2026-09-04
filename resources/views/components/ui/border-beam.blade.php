@props([
'size' => 64,
'duration' => 6,
'delay' => 0,
'reverse' => false,
'colorFrom' => 'var(--brand-from)',
'colorTo' => 'var(--brand-to)',
])

<div
    aria-hidden="true"
    data-slot="border-beam"
    class="pointer-events-none absolute inset-0 rounded-[inherit] border border-transparent [mask-clip:padding-box,border-box] [mask-composite:intersect] [mask-image:linear-gradient(transparent,transparent),linear-gradient(#000,#000)] motion-reduce:hidden"
>
    <div
        @class([
            'absolute aspect-square bg-gradient-to-l from-(--beam-from) via-(--beam-to) to-transparent animate-border-beam',
            '[animation-direction:reverse]' => $reverse,
        ])
        style="
            width: {{ $size }}px;
            offset-path: rect(0 auto auto 0 round {{ $size }}px);
            --beam-from: {{ $colorFrom }};
            --beam-to: {{ $colorTo }};
            --beam-duration: {{ $duration }}s;
            --beam-delay: -{{ $delay }}s;
            "
    ></div>
</div>
