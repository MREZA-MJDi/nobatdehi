<section
    id="featured"
    class="discover-section discover-featured"
>
    <div class="discover-container">

        <x-discover.section-heading
            eyebrow="پیشنهاد NOBAT"
            title="یک سالن خوب برای شروع"
            description="یک انتخاب شاخص برای وقتی که می‌خواهی سریع‌تر تصمیم بگیری."
        />

        @if($featuredSalon)

            <article class="discover-featured-card reveal-item">

                <div class="discover-featured-media">

                    @if($featuredSalon->cover_url)

                        <img
                            src="{{ $featuredSalon->cover_url }}"
                            alt="{{ $featuredSalon->name }}"
                            loading="lazy"
                            decoding="async"
                        >

                    @else

                        <div
                            class="discover-featured-placeholder"
                            aria-hidden="true"
                        >
                            <span>
                                {{ mb_substr(trim($featuredSalon->name), 0, 1) }}
                            </span>
                        </div>

                    @endif

                </div>


                <div class="discover-featured-content">

                    <span class="discover-eyebrow">
                        سالن منتخب
                    </span>

                    <h2>
                        {{ $featuredSalon->name }}
                    </h2>


                    @if($featuredSalon->district || $featuredSalon->city)

                        <p class="discover-featured-location">
                            {{ collect([
                                $featuredSalon->district,
                                $featuredSalon->city,
                            ])->filter()->implode('، ') }}
                        </p>

                    @endif


                    @if(!empty($featuredSalon->description))

                        <p class="discover-featured-description">
                            {{ \Illuminate\Support\Str::limit(
                                strip_tags($featuredSalon->description),
                                220
                            ) }}
                        </p>

                    @else

                        <p class="discover-featured-description">
                            برای دیدن خدمات، متخصص‌ها، نمونه‌کارها و
                            اطلاعات کامل این سالن وارد صفحه اختصاصی آن شو.
                        </p>

                    @endif


                    <div class="discover-featured-meta">

                        @if(
                            isset($featuredSalon->reviews_avg_rating)
                            && $featuredSalon->reviews_avg_rating
                        )

                            <span>
                                <strong>
                                    {{ number_format(
                                        (float) $featuredSalon->reviews_avg_rating,
                                        1
                                    ) }}
                                </strong>

                                <span aria-hidden="true">
                                    ★
                                </span>
                            </span>

                        @endif


                        @if(isset($featuredSalon->reviews_count))

                            <span>
                                {{ number_format($featuredSalon->reviews_count) }}
                                نظر
                            </span>

                        @endif


                        @if(isset($featuredSalon->services_count))

                            <span>
                                {{ number_format($featuredSalon->services_count) }}
                                خدمت
                            </span>

                        @endif

                    </div>


                    <x-discover.primary-button
                        :href="route('public.salons.show', $featuredSalon)"
                        text="مشاهده سالن"
                    />

                </div>

            </article>

        @else

            <div class="discover-empty">

                <h3>
                    هنوز سالن منتخبی برای نمایش نداریم.
                </h3>

                <p>
                    سالن‌های فعال NOBAT به‌زودی اینجا نمایش داده می‌شوند.
                </p>

            </div>

        @endif

    </div>
</section>
