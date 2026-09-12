<section
    class="discovery-section"
    id="stylists"
>

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    متخصص‌ها
                </span>

                <h2 class="discovery-section-title">
                    متخصص مورد علاقه‌ات را پیدا کن
                </h2>

                <p class="discovery-section-description">
                    متخصص مناسب خودت را پیدا کن و وارد سالن شو.
                </p>

            </div>

        </div>


        <div class="discovery-stylists-grid">

            @forelse(
                $salons
                    ->flatMap->barbers
                    ->take(8)
                as $barber
            )

                <a
                    href="{{ route(
                        'public.salons.show',
                        $barber->salon
                    ) }}"
                    class="discovery-stylist-card"
                >

                    <div class="discovery-stylist-image">

                        @if($barber->image_path)

                            <img
                                src="{{ $resolveImage(
                                    $barber->image_path
                                ) }}"
                                alt="{{ $barber->name }}"
                                loading="lazy"
                            >

                        @else

                            <div
                                class="discovery-stylist-placeholder"
                            >
                                {{ mb_substr(
                                    trim($barber->name),
                                    0,
                                    1
                                ) }}
                            </div>

                        @endif

                    </div>


                    <div class="discovery-stylist-body">

                        <h3 class="discovery-stylist-name">
                            {{ $barber->name }}
                        </h3>

                        <div class="discovery-stylist-role">
                            {{
                                $barber->specialty
                                ?: 'متخصص زیبایی'
                            }}
                        </div>

                        <div class="discovery-stylist-location">
                            {{ $barber->salon?->name }}
                        </div>


                        <div class="discovery-stylist-bottom">

                            <span class="discovery-stylist-rating">
                                ★ متخصص
                            </span>

                            <span class="discovery-stylist-button">
                                پروفایل ←
                            </span>

                        </div>

                    </div>

                </a>

            @empty

                <div class="discovery-empty">
                    هنوز متخصصی برای نمایش وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
