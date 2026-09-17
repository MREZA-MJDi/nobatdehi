<section class="discover-section discover-section-soft">

    <div class="discover-container">

        <div class="discover-section-heading discover-section-heading-inline">

            <div>

                <span class="discover-kicker">
                    محبوب‌ها
                </span>

                <h2>
                    سالن‌هایی که بیشتر دیده می‌شوند
                </h2>

                <p>
                    چند گزینه برای شروع انتخابت.
                </p>

            </div>

            <a
                href="{{ route('salons.discover') }}#results"
                class="discover-section-link"
            >
                مشاهده همه
                ←
            </a>

        </div>


        <div class="discover-salon-grid">

            @forelse(
                $popularSalons as $salon
            )

                @php
                    $salonImage =
                        ($salon->cover_url ?? null)
                        ?: $resolveImage(
                            $salon->cover_path ?? null
                        )
                        ?: $resolveImage(
                            $salon->logo_path ?? null
                        );

                    $rating = (float) (
                        $salon->reviews_avg_rating ?? 0
                    );

                    $location = collect([
                        $salon->district,
                        $salon->city,
                        $salon->province,
                    ])->filter()->implode('، ');
                @endphp

                <a
                    href="{{ route(
                        'public.salons.show',
                        $salon
                    ) }}"
                    class="discover-salon-card"
                >

                    <div class="discover-salon-media">

                        @if($salonImage)

                            <img
                                src="{{ $salonImage }}"
                                alt="{{ $salon->name }}"
                                loading="lazy"
                            >

                        @else

                            <div class="discover-salon-fallback">
                                NOBAT
                            </div>

                        @endif

                        @if($rating > 0)

                            <span class="discover-rating-badge">
                                ★
                                {{ number_format(
                                    $rating,
                                    1
                                ) }}
                            </span>

                        @endif

                    </div>


                    <div class="discover-salon-body">

                        <h3>
                            {{ $salon->name }}
                        </h3>

                        <p>
                            {{ $location ?: 'موقعیت ثبت نشده' }}
                        </p>


                        <div class="discover-salon-meta">

                            <span>
                                {{ number_format(
                                    (int) $salon->services_count
                                ) }}
                                خدمت
                            </span>

                            <span>
                                {{ number_format(
                                    (int) $salon->barbers_count
                                ) }}
                                متخصص
                            </span>

                        </div>


                        <div class="discover-salon-footer">

                            <span>
                                مشاهده سالن
                            </span>

                            <span aria-hidden="true">
                                ←
                            </span>

                        </div>

                    </div>

                </a>

            @empty

                <div class="discover-empty-inline">
                    هنوز سالنی برای نمایش وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
