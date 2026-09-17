@if($nearbySalons->isNotEmpty())
    @php
        $geoPoints = $nearbySalons
            ->map(function ($salon) {
                $lat = $salon->latitude;
                $lng = $salon->longitude;

                if (!is_numeric($lat) || !is_numeric($lng)) {
                    return null;
                }

                return [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                ];
            })
            ->filter()
            ->values();

        $mapUrl = null;

        if ($geoPoints->isNotEmpty()) {
            $minLat = $geoPoints->min('lat');
            $maxLat = $geoPoints->max('lat');
            $minLng = $geoPoints->min('lng');
            $maxLng = $geoPoints->max('lng');

            $centerLat = ($minLat + $maxLat) / 2;
            $centerLng = ($minLng + $maxLng) / 2;

            $latPadding = max(.01, ($maxLat - $minLat) * .22);
            $lngPadding = max(.01, ($maxLng - $minLng) * .22);

            $mapUrl = sprintf(
                'https://www.openstreetmap.org/export/embed.html?bbox=%s,%s,%s,%s&layer=mapnik&marker=%s,%s',
                $minLng - $lngPadding,
                $minLat - $latPadding,
                $maxLng + $lngPadding,
                $maxLat + $latPadding,
                $centerLat,
                $centerLng
            );
        }
    @endphp

    <section
        class="discover-section discover-section-soft"
        id="nearby"
    >
        <div class="discover-container">
            <div class="discover-section-heading discover-section-heading-inline">
                <div>
                    <span class="discover-kicker">نزدیک شما</span>
                    <h2>سالن‌های اطراف تو</h2>
                    <p>
                        @if($nearbyRadius <= 2)
                            چند سالن در شعاع ۲ کیلومتری از موقعیتت پیدا کردیم.
                        @elseif($nearbyRadius <= 5)
                            در شعاع {{ number_format($nearbyRadius, 0) }} کیلومتری چند گزینه نزدیک پیدا کردیم.
                        @else
                            در شعاع {{ rtrim(rtrim(number_format($nearbyRadius, 1), '0'), '.') }} کیلومتری گزینه‌هایی برایت پیدا کردیم.
                        @endif
                    </p>
                </div>

                <a
                    href="{{ route('salons.discover') }}#results"
                    class="discover-section-link"
                >
                    تغییر موقعیت
                    <span aria-hidden="true">←</span>
                </a>
            </div>

            <div class="discover-nearby-info">
                <span>نزدیک‌ترین گزینه‌ها</span>
                <strong>
                    تا
                    {{ rtrim(rtrim(number_format($nearbyRadius, 1), '0'), '.') }}
                    کیلومتر
                </strong>
            </div>

            <div class="discover-nearby-layout">
                @if($mapUrl)
                    <div class="discover-nearby-map" aria-label="نقشه سالن‌های نزدیک">
                        <iframe
                            src="{{ $mapUrl }}"
                            title="موقعیت تقریبی سالن‌های نزدیک"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                        <span class="discover-nearby-map-credit">
                            نقشه بر پایه OpenStreetMap
                        </span>
                    </div>
                @endif

                <div class="discover-nearby-grid">
                    @foreach($nearbySalons as $salon)
                        @php
                            $image =
                                ($salon->cover_url ?? null)
                                ?: $resolveImage($salon->cover_path ?? null)
                                ?: $resolveImage($salon->logo_path ?? null);

                            $rating = (float) ($salon->reviews_avg_rating ?? 0);

                            $location = collect([
                                $salon->district,
                                $salon->city,
                            ])
                            ->filter()
                            ->implode('، ');
                        @endphp

                        <article class="discover-nearby-card">
                            <a
                                href="{{ route('public.salons.show', $salon) }}"
                                class="discover-nearby-media"
                            >
                                @if($image)
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $salon->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="discover-salon-fallback">NOBAT</div>
                                @endif
                            </a>

                            <div class="discover-nearby-body">
                                <div class="discover-nearby-top">
                                    <div>
                                        <h3>{{ $salon->name }}</h3>
                                        <p>{{ $location ?: 'ایران' }}</p>
                                    </div>

                                    @if($rating > 0)
                                        <span class="discover-nearby-rating">
                                            ★ {{ number_format($rating, 1) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="discover-nearby-meta">
                                    @if(isset($salon->distance_km))
                                        <span>
                                            📍
                                            {{ number_format((float) $salon->distance_km, 1) }}
                                            کیلومتر
                                        </span>
                                    @endif
                                    <span>{{ number_format((int) $salon->services_count) }} خدمت</span>
                                    <span>{{ number_format((int) $salon->barbers_count) }} متخصص</span>
                                </div>

                                @if($salon->services->isNotEmpty())
                                    <div class="discover-result-tags">
                                        @foreach($salon->services->take(3) as $service)
                                            <span>{{ $service->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="discover-nearby-footer">
                                    <span>نزدیک شما</span>
                                    <a
                                        href="{{ route('public.salons.show', $salon) }}"
                                        class="discover-result-button"
                                    >
                                        مشاهده سالن
                                        <span aria-hidden="true">←</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
