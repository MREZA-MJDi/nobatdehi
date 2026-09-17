@if($featuredSalon)

    @php
        $featuredImage =
            ($featuredSalon->cover_url ?? null)
            ?: $resolveImage(
                $featuredSalon->cover_path ?? null
            )
            ?: $resolveImage(
                $featuredSalon->logo_path ?? null
            );

        $featuredRating = (float) (
            $featuredSalon->reviews_avg_rating ?? 0
        );

        $featuredLocation = collect([
            $featuredSalon->district,
            $featuredSalon->city,
            $featuredSalon->province,
        ])->filter()->implode('، ');
    @endphp

    <section class="discover-section">

        <div class="discover-container">

            <div class="discover-featured">

                <div class="discover-featured-media">

                    @if($featuredImage)

                        <img
                            src="{{ $featuredImage }}"
                            alt="{{ $featuredSalon->name }}"
                            loading="lazy"
                        >

                    @else

                        <div class="discover-salon-fallback">
                            NOBAT
                        </div>

                    @endif

                </div>


                <div class="discover-featured-content">

                    <span class="discover-kicker">
                        NOBAT SELECTED
                    </span>

                    <h2>
                        {{ $featuredSalon->name }}
                    </h2>

                    <div class="discover-featured-meta">

                        @if($featuredRating > 0)

                            <span>
                                ★
                                {{ number_format(
                                    $featuredRating,
                                    1
                                ) }}
                            </span>

                        @endif

                        <span>
                            {{ $featuredLocation ?: 'ایران' }}
                        </span>

                    </div>

                    <p>
                        {{ Str::limit(
                            strip_tags(
                                $featuredSalon->description
                                ?: 'یکی از سالن‌های منتخب NOBAT.'
                            ),
                            220
                        ) }}
                    </p>


                    <a
                        href="{{ route(
                            'public.salons.show',
                            $featuredSalon
                        ) }}"
                        class="discover-primary-button"
                    >
                        مشاهده و رزرو
                        <span aria-hidden="true">
                            ←
                        </span>
                    </a>

                </div>

            </div>

        </div>

    </section>

@endif
