@props([
'barber',
])

@php
    $salon = $barber->salon;
@endphp

<article class="discover-stylist-card reveal-item">

    <a
        href="{{ $salon ? route('public.salons.show', $salon) : '#' }}"
        class="discover-stylist-media"
        aria-label="{{ $barber->name }}"
    >

        @if(!empty($barber->image_path))

            <img
                src="{{ asset('storage/' . ltrim($barber->image_path, '/')) }}"
                alt="{{ $barber->name }}"
                loading="lazy"
                decoding="async"
            >

        @else

            <div
                class="discover-stylist-placeholder"
                aria-hidden="true"
            >
                <span>
                    {{ mb_substr(trim($barber->name), 0, 1) }}
                </span>
            </div>

        @endif

    </a>


    <div class="discover-stylist-content">

        <span class="discover-eyebrow">
            متخصص
        </span>

        <h3>
            {{ $barber->name }}
        </h3>

        @if($barber->specialty)

            <p>
                {{ $barber->specialty }}
            </p>

        @endif

        @if($salon)

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="discover-stylist-salon"
            >
                {{ $salon->name }}

                <span aria-hidden="true">
                    ↗
                </span>
            </a>

        @endif

    </div>

</article>
