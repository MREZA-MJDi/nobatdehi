@props([
'salon',
])

@php
    $location = collect([
        $salon->district ?? null,
        $salon->city ?? null,
        $salon->province ?? null,
    ])
    ->filter()
    ->unique()
    ->implode('، ');

    $rating = $salon->reviews_avg_rating ?? null;
    $reviewsCount = $salon->reviews_count ?? null;
@endphp

<article class="discover-salon-card reveal-item">

    <a
        href="{{ route('public.salons.show', $salon) }}"
        class="discover-salon-card-media"
        aria-label="مشاهده {{ $salon->name }}"
    >

        @if($salon->cover_url)

            <img
                src="{{ $salon->cover_url }}"
                alt="{{ $salon->name }}"
                loading="lazy"
                decoding="async"
            >

        @else

            <div
                class="discover-salon-placeholder"
                aria-hidden="true"
            >
                <span>
                    {{ mb_substr(trim($salon->name), 0, 1) }}
                </span>
            </div>

        @endif

        @if($rating)

            <div class="discover-salon-rating">

                <span aria-hidden="true">
                    ★
                </span>

                <strong>
                    {{ number_format((float) $rating, 1) }}
                </strong>

                @if($reviewsCount !== null)
                    <small>
                        ({{ number_format($reviewsCount) }})
                    </small>
                @endif

            </div>

        @endif

    </a>


    <div class="discover-salon-card-body">

        <div class="discover-salon-card-top">

            <div>

                <h3>
                    <a href="{{ route('public.salons.show', $salon) }}">
                        {{ $salon->name }}
                    </a>
                </h3>

                @if($location)

                    <p class="discover-salon-location">
                        <span aria-hidden="true">⌖</span>
                        {{ $location }}
                    </p>

                @endif

            </div>

        </div>


        @if($salon->services->isNotEmpty())

            <div
                class="discover-salon-tags"
                aria-label="خدمات"
            >

                @foreach($salon->services->take(3) as $service)

                    <span>
                        {{ $service->name }}
                    </span>

                @endforeach

            </div>

        @endif


        <div class="discover-salon-card-footer">

            <a
                href="{{ route('public.salons.show', $salon) }}"
                class="discover-salon-action"
            >
                مشاهده سالن

                <span aria-hidden="true">
                    ←
                </span>
            </a>

        </div>

    </div>

</article>
