<section
    class="discovery-section"
    id="salons"
>

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    سالن‌های محبوب
                </span>

                <h2 class="discovery-section-title">
                    محبوب این روزها
                </h2>

                <p class="discovery-section-description">
                    سالن‌هایی که کاربران بیشتر دیده و انتخاب کرده‌اند.
                </p>

            </div>

            <a
                href="{{ route('salons.discover') }}"
                class="discovery-section-link"
            >
                مشاهده همه
                <span>←</span>
            </a>

        </div>


        <div class="discovery-salons-grid">

            @forelse(
                $salons->take(6)
                as $salon
            )

                <a
                    href="{{ route(
                        'public.salons.show',
                        $salon
                    ) }}"
                    class="discovery-salon-card"
                >

                    <div class="discovery-salon-image">

                        @if($salon->cover_url ?? null)

                            <img
                                src="{{ $salon->cover_url }}"
                                alt="{{ $salon->name }}"
                                loading="lazy"
                            >

                        @elseif($salon->cover_path)

                            <img
                                src="{{ $resolveImage(
                                    $salon->cover_path
                                ) }}"
                                alt="{{ $salon->name }}"
                                loading="lazy"
                            >

                        @else

                            <div
                                class="discovery-salon-image-fallback"
                            >
                                NOBAT
                            </div>

                        @endif


                        <span
                            class="discovery-salon-favorite"
                            aria-hidden="true"
                        >
                            ♡
                        </span>

                    </div>


                    <div class="discovery-salon-body">

                        <div class="discovery-salon-name-row">

                            <h3 class="discovery-salon-name">
                                {{ $salon->name }}
                            </h3>

                            @if(isset($salon->rating))

                                <span class="discovery-salon-rating">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="m12 2.8 2.78 5.63 6.22.9-4.5 4.38 1.06 6.2L12 16.98 6.44 19.9l1.06-6.2-4.5-4.38 6.22-.9L12 2.8Z"
                                        />
                                    </svg>

                                    {{ number_format(
                                        (float) $salon->rating,
                                        1
                                    ) }}

                                </span>

                            @endif

                        </div>


                        <div class="discovery-salon-location">

                            <span>
                                📍
                            </span>

                            <span>
                                {{
                                    collect([
                                        $salon->district,
                                        $salon->city,
                                        $salon->province,
                                    ])
                                    ->filter()
                                    ->first()
                                    ?: 'ایران'
                                }}
                            </span>

                        </div>


                        @if(
                            $salon->services
                            ->isNotEmpty()
                        )

                            <div class="discovery-salon-meta">

                                @foreach(
                                    $salon->services->take(3)
                                    as $service
                                )

                                    <span class="discovery-salon-tag">
                                        {{ $service->name }}
                                    </span>

                                @endforeach

                            </div>

                        @endif


                        <div class="discovery-salon-footer">

                            @php
                                $firstService =
                                    $salon->services->first();
                            @endphp

                            <div class="discovery-salon-price">

                                شروع قیمت

                                <strong>
                                    {{
                                        $firstService?->price !== null
                                            ? number_format(
                                                (int) $firstService->price
                                            ) . ' تومان'
                                            : 'توافقی'
                                    }}
                                </strong>

                            </div>

                            <span class="discovery-salon-button">
                                مشاهده سالن →
                            </span>

                        </div>

                    </div>

                </a>

            @empty

                <div class="discovery-empty">
                    هنوز سالنی برای نمایش وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
