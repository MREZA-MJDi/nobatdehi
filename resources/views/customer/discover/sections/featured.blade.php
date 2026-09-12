@if($featuredSalon)

    <section class="discovery-section">

        <div class="discovery-container">

            <div class="discovery-featured-card">

                <div class="discovery-featured-image">

                    @if($featuredSalon->cover_url ?? null)

                        <img
                            src="{{ $featuredSalon->cover_url }}"
                            alt="{{ $featuredSalon->name }}"
                            loading="lazy"
                        >

                    @elseif($featuredSalon->cover_path)

                        <img
                            src="{{ $resolveImage(
                            $featuredSalon->cover_path
                        ) }}"
                            alt="{{ $featuredSalon->name }}"
                            loading="lazy"
                        >

                    @else

                        <div
                            class="discovery-salon-image-fallback"
                        >
                            NOBAT
                        </div>

                    @endif

                </div>


                <div class="discovery-featured-content">

                <span class="discovery-featured-kicker">
                    NOBAT SELECTED
                </span>

                    <h3>
                        {{ $featuredSalon->name }}
                    </h3>


                    <div class="discovery-featured-meta">

                    <span class="discovery-featured-meta-item">
                        ★

                        {{
                            isset($featuredSalon->rating)
                                ? number_format(
                                    (float) $featuredSalon->rating,
                                    1
                                )
                                : '—'
                        }}
                    </span>


                        <span class="discovery-featured-meta-item">
                        📍

                        {{
                            collect([
                                $featuredSalon->district,
                                $featuredSalon->city,
                                $featuredSalon->province,
                            ])
                            ->filter()
                            ->implode('، ')
                            ?: 'ایران'
                        }}
                    </span>

                    </div>


                    <p>

                        {{
                            \Illuminate\Support\Str::limit(
                                strip_tags(
                                    $featuredSalon->description
                                    ?: 'یکی از سالن‌های منتخب NOBAT برای تجربه‌ای متفاوت.'
                                ),
                                220
                            )
                        }}

                    </p>


                    <a
                        href="{{ route(
                        'public.salons.show',
                        $featuredSalon
                    ) }}"
                        class="discovery-featured-button"
                    >
                        مشاهده و رزرو
                        <span>←</span>
                    </a>

                </div>

            </div>

        </div>

    </section>

@endif
