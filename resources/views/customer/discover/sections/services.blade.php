<section
    class="discovery-section"
    id="services"
>

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    خدمات
                </span>

                <h2 class="discovery-section-title">
                    دنبال چه چیزی هستی؟
                </h2>

                <p class="discovery-section-description">
                    خدمت موردنظرت را انتخاب کن و سالن مناسب را پیدا کن.
                </p>

            </div>

            <a
                href="{{ route('salons.discover') }}"
                class="discovery-section-link"
            >
                همه خدمات
                <span>←</span>
            </a>

        </div>


        <div class="discovery-services-grid">

            @forelse(
                $services->take(6)
                as $service
            )

                @php

                    $serviceName =
                        trim($service->name ?? '');

                    $serviceImage =
                        $resolveImage(
                            $service->image_path
                            ?? $service->image
                            ?? null
                        );

                    if (!$serviceImage) {

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

                    $serviceImage =
                        $serviceImage
                        ?: $defaultServiceImage;

                @endphp


                <a
                    href="{{ route(
                        'salons.discover',
                        ['q' => $serviceName]
                    ) }}"
                    class="discovery-service-card"
                >

                    <img
                        src="{{ $serviceImage }}"
                        alt="{{ $serviceName }}"
                        loading="lazy"
                    >

                    <div class="discovery-service-content">

                        <span>
                            NOBAT SERVICE
                        </span>

                        <strong>
                            {{ $serviceName }}
                        </strong>

                    </div>

                </a>

            @empty

                <div class="discovery-empty">
                    هنوز خدمتی برای نمایش وجود ندارد.
                </div>

            @endforelse

        </div>

    </div>

</section>
