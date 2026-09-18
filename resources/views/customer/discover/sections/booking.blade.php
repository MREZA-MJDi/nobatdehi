<section class="discovery-section">

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    BOOKING
                </span>

                <h2 class="discovery-section-title">
                    همین هفته نوبت بگیر
                </h2>

                <p class="discovery-section-description">
                    سالن را انتخاب کن و در چند قدم نوبتت را ثبت کن.
                </p>

            </div>

        </div>


        <div class="discovery-booking-grid">

            @forelse(
                $salons->take(3)
                as $salon
            )

                <article class="discovery-booking-card">

                    <div class="discovery-booking-card-head">

                        <div>

                            <h3 class="discovery-booking-card-name">
                                {{ $salon->name }}
                            </h3>

                            <div class="discovery-booking-card-city">
                                {{
                                    collect([
                                        $salon->district,
                                        $salon->city,
                                    ])
                                    ->filter()
                                    ->implode('، ')
                                    ?: 'ایران'
                                }}
                            </div>

                        </div>

                    </div>


                    <div class="discovery-booking-slots">

                        <span class="discovery-booking-slot">
                            انتخاب خدمت
                        </span>

                        <span class="discovery-booking-slot">
                            انتخاب متخصص
                        </span>

                        <span class="discovery-booking-slot">
                            رزرو آنلاین
                        </span>

                    </div>


                    <div class="discovery-booking-footer">

                        <span class="discovery-booking-day">
                            سریع و آنلاین
                        </span>

                        <a
                            href="{{ route(
                                'public.salons.show',
                                $salon
                            ) }}"
                            class="discovery-booking-button"
                        >
                            رزرو نوبت
                        </a>

                    </div>

                </article>

            @empty

                <div class="discovery-empty">
                    هنوز سالنی برای رزرو وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
