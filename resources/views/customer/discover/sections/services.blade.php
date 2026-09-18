<section
    class="discover-section"
    id="services"
>

    <div class="discover-container">

        <div class="discover-section-heading">

            <span class="discover-kicker">
                خدمات
            </span>

            <h2>
                برای چه خدمتی دنبال سالن هستی؟
            </h2>

            <p>
                از خدمات محبوب شروع کن یا وارد جستجوی کامل شو.
            </p>

        </div>


        <div class="discover-category-grid">

            @foreach(
                collect($serviceCategories)->take(9)
                as $category
            )

                <a
                    href="{{ route(
                        'salons.discover',
                        ['q' => $category['label']]
                    ) }}#results"
                    class="discover-category-card"
                >

                    <span class="discover-category-icon">
                        {{ mb_substr($category['label'], 0, 1) }}
                    </span>

                    <strong>
                        {{ $category['label'] }}
                    </strong>

                    <span>
                        پیدا کردن سالن
                        ←
                    </span>

                </a>

            @endforeach

        </div>


        <div class="discover-service-section">

            <div class="discover-section-heading discover-section-heading-inline">

                <div>

                    <span class="discover-kicker">
                        پرتقاضا
                    </span>

                    <h2>
                        خدمات محبوب
                    </h2>

                </div>

                <a
                    href="{{ route('salons.discover') }}#results"
                    class="discover-section-link"
                >
                    همه خدمات
                    ←
                </a>

            </div>


            <div class="discover-service-grid">

                @forelse(
                    $popularServices->take(6)
                    as $service
                )

                    @php
                        $serviceName = trim(
                            $service->name ?? ''
                        );

                        $serviceImage =
                            $resolveImage(
                                $service->image_path ?? null
                            );

                        if (! $serviceImage) {
                            foreach (
                                $serviceFallbacks
                                as $keyword => $image
                            ) {
                                if (
                                    str_contains(
                                        $serviceName,
                                        $keyword
                                    )
                                ) {
                                    $serviceImage = $image;
                                    break;
                                }
                            }
                        }

                        $serviceImage ??= $heroImage;
                    @endphp

                    <a
                        href="{{ route(
                            'salons.discover',
                            ['service' => $serviceName]
                        ) }}#results"
                        class="discover-service-card"
                    >

                        <div class="discover-service-image">

                            <img
                                src="{{ $serviceImage }}"
                                alt="{{ $serviceName }}"
                                loading="lazy"
                            >

                        </div>


                        <div class="discover-service-body">

                            <span>
                                NOBAT SERVICE
                            </span>

                            <strong>
                                {{ $serviceName }}
                            </strong>

                            <small>
                                از
                                {{ number_format(
                                    (int) $service->price
                                ) }}
                                تومان
                            </small>

                        </div>

                    </a>

                @empty

                    <div class="discover-empty-inline">
                        هنوز خدمتی برای نمایش وجود ندارد.
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</section>
