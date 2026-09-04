@extends('layouts.customer')

@section('title', $seoTitle)

@section('meta_description', $seoDescription)

@section('robots', $robots)


@push('head')

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        crossorigin=""
    >

@endpush


@section('content')

    <div
        id="discover-page"
        class="discover-page"
    >


        {{-- ==========================================================
            HERO
        =========================================================== --}}

        <section class="discover-hero">

            <div class="discover-hero-glow discover-hero-glow-one"></div>
            <div class="discover-hero-glow discover-hero-glow-two"></div>
            <div class="discover-grid"></div>


            <div class="customer-container discover-hero-inner">

                <div
                    class="discover-hero-copy"
                    data-blur-fade
                >

                <span class="discover-eyebrow">
                    RM MARKETPLACE
                </span>

                    <h1>
                        سالن خوب پیدا کن،
                        <span>نوبتش رو بگیر.</span>
                    </h1>

                    <p>
                        سالن‌ها و متخصص‌های اطراف خودت را مقایسه کن،
                        نظر مشتری‌ها را ببین و زمان خالی واقعی را پیدا کن.
                    </p>

                </div>


                {{-- Search --}}
                <form
                    method="GET"
                    action="{{ route('salons.discover') }}"
                    class="discover-search"
                    data-blur-fade
                >

                    <div class="discover-search-main">

                    <span class="discover-search-icon">
                        ⌕
                    </span>

                        <input
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            placeholder="سالن، آرایشگر یا خدماتی که می‌خوای..."
                            autocomplete="off"
                        >

                    </div>


                    <div class="discover-search-city">

                        <select name="city">

                            <option value="">
                                همه شهرها
                            </option>

                            @foreach($cities as $availableCity)

                                <option
                                    value="{{ $availableCity }}"
                                    @selected($city === $availableCity)
                                >
                                    {{ $availableCity }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <input
                        type="hidden"
                        name="lat"
                        id="discover-latitude"
                        value="{{ $latitude }}"
                    >

                    <input
                        type="hidden"
                        name="lng"
                        id="discover-longitude"
                        value="{{ $longitude }}"
                    >


                    <button
                        type="button"
                        id="discover-location"
                        class="discover-location-button"
                        title="نزدیک من"
                    >
                        ◎
                    </button>


                    <button
                        type="submit"
                        class="discover-search-submit"
                    >
                        جستجو
                    </button>

                </form>


                {{-- Quick links --}}
                <div
                    class="discover-quick-links"
                    data-blur-fade
                >

                    <a
                        href="{{ route('salons.discover', ['sort' => 'nearest']) }}"
                    >
                        نزدیک من
                    </a>

                    <a
                        href="{{ route('salons.discover', ['sort' => 'rating']) }}"
                    >
                        محبوب‌ترین
                    </a>

                    <a
                        href="{{ route('salons.discover', ['type' => 'barber']) }}"
                    >
                        متخصص‌ها
                    </a>

                </div>

            </div>

        </section>


        {{-- ==========================================================
            STATS
        =========================================================== --}}

        <section class="discover-stats">

            <div class="customer-container discover-stats-grid">

                <div
                    class="discover-stat"
                    data-number-ticker
                    data-number-ticker-value="{{ $activeSalonCount }}"
                >
                    <strong>
                        {{ $activeSalonCount }}
                    </strong>

                    <span>
                    سالن فعال
                </span>
                </div>


                <div
                    class="discover-stat"
                    data-number-ticker
                    data-number-ticker-value="{{ $activeBarberCount }}"
                >
                    <strong>
                        {{ $activeBarberCount }}
                    </strong>

                    <span>
                    متخصص
                </span>
                </div>


                <div
                    class="discover-stat"
                    data-number-ticker
                    data-number-ticker-value="{{ $publishedReviewCount }}"
                >
                    <strong>
                        {{ $publishedReviewCount }}
                    </strong>

                    <span>
                    نظر واقعی
                </span>
                </div>

            </div>

        </section>


        {{-- ==========================================================
            CATEGORIES
        =========================================================== --}}

        <section class="customer-section">

            <div class="customer-container">

                <div
                    class="customer-section-heading"
                    data-blur-fade
                >

                    <div>

                    <span>
                        BROWSE
                    </span>

                        <h2>
                            دنبال چه خدمتی هستی؟
                        </h2>

                    </div>

                    <a href="{{ route('salons.discover') }}">
                        همه
                    </a>

                </div>


                <div class="category-strip">

                    @foreach($categories as $category)

                        <a
                            href="{{ route(
                            'salons.discover',
                            ['q' => $category['query']]
                        ) }}"
                            class="category-card"
                            data-spotlight-card
                        >

                        <span class="category-card-icon">
                            ✦
                        </span>

                            <strong>
                                {{ $category['title'] }}
                            </strong>

                            <small>
                                {{ $category['subtitle'] }}
                            </small>

                        </a>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- ==========================================================
            RESULTS
        =========================================================== --}}

        <section class="customer-section customer-section-soft">

            <div class="customer-container">

                <div class="discover-results-toolbar">

                    <div>

                    <span class="section-kicker">
                        DISCOVER
                    </span>

                        <h2>
                            {{ $query || $city
                                ? 'نتایج جستجو'
                                : 'پیشنهادهای RM'
                            }}
                        </h2>

                    </div>


                    <div class="discover-sort">

                        <a
                            href="{{ request()->fullUrlWithQuery([
                            'sort' => 'recommended'
                        ]) }}"
                            @class([
                                'is-active' =>
                                    $sort === 'recommended',
                            ])
                        >
                            پیشنهادی
                        </a>

                        <a
                            href="{{ request()->fullUrlWithQuery([
                            'sort' => 'rating'
                        ]) }}"
                            @class([
                                'is-active' =>
                                    $sort === 'rating',
                            ])
                        >
                            محبوب‌ترین
                        </a>

                        <a
                            href="{{ request()->fullUrlWithQuery([
                            'sort' => 'newest'
                        ]) }}"
                            @class([
                                'is-active' =>
                                    $sort === 'newest',
                            ])
                        >
                            جدیدترین
                        </a>

                        @if($hasLocation)

                            <a
                                href="{{ request()->fullUrlWithQuery([
                                'sort' => 'nearest'
                            ]) }}"
                                @class([
                                    'is-active' =>
                                        $sort === 'nearest',
                                ])
                            >
                                نزدیک‌ترین
                            </a>

                        @endif

                    </div>

                </div>


                @if($salons->count())

                    <div class="salon-grid">

                        @foreach($salons as $salon)

                            @php
                                $coverUrl = $salon->cover_path
                                    ? \Illuminate\Support\Facades\Storage::url(
                                        $salon->cover_path
                                    )
                                    : null;

                                $logoUrl = $salon->logo_path
                                    ? \Illuminate\Support\Facades\Storage::url(
                                        $salon->logo_path
                                    )
                                    : null;
                            @endphp


                            <article
                                class="salon-market-card"
                                data-blur-fade
                                data-spotlight-card
                            >

                                <a
                                    href="{{ route(
                                    'public.salons.show',
                                    $salon
                                ) }}"
                                    class="salon-market-cover"
                                >

                                    @if($coverUrl)

                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $salon->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <div
                                            class="salon-market-cover-placeholder"
                                            style="
                                                --salon-a:
                                            {{ $salon->primary_color ?: '#6757e8' }};
                                                --salon-b:
                                            {{ $salon->secondary_color ?: '#37b8c8' }};
                                                "
                                        ></div>

                                    @endif


                                    <span class="salon-market-badge">
                                    فعال
                                </span>


                                    @if(isset($salon->distance_km))

                                        <span class="salon-distance">
                                        {{ number_format(
                                            $salon->distance_km,
                                            1
                                        ) }}
                                        km
                                    </span>

                                    @endif

                                </a>


                                <div class="salon-market-body">

                                    <div class="salon-market-heading">

                                        <div class="salon-market-logo">

                                            @if($logoUrl)

                                                <img
                                                    src="{{ $logoUrl }}"
                                                    alt=""
                                                    loading="lazy"
                                                >

                                            @else

                                                {{ mb_substr(
                                                    $salon->name,
                                                    0,
                                                    1
                                                ) }}

                                            @endif

                                        </div>


                                        <div>

                                            <h3>
                                                {{ $salon->name }}
                                            </h3>

                                            <p>
                                                {{ collect([
                                                    $salon->city,
                                                    $salon->district,
                                                ])->filter()->join('، ') }}
                                            </p>

                                        </div>

                                    </div>


                                    <div class="salon-market-rating">

                                        <strong>
                                            {{ number_format(
                                                (float) (
                                                    $salon->reviews_avg_rating
                                                    ?? 0
                                                ),
                                                1
                                            ) }}
                                        </strong>

                                        <span>
                                        ★
                                    </span>

                                        <small>
                                            {{ $salon->reviews_count }}
                                            نظر
                                        </small>

                                    </div>


                                    <div class="salon-market-meta">

                                    <span>
                                        {{ $salon->barbers_count }}
                                        متخصص
                                    </span>

                                        <span>
                                        {{ $salon->services_count }}
                                        خدمت
                                    </span>

                                    </div>


                                    <a
                                        href="{{ route(
                                        'public.salons.booking.create',
                                        $salon
                                    ) }}"
                                        class="salon-market-cta"
                                    >
                                        رزرو نوبت
                                        <span>←</span>
                                    </a>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    <div class="discover-pagination">

                        {{ $salons->links() }}

                    </div>

                @else

                    <div class="discover-empty">

                        <div class="discover-empty-icon">
                            ⌕
                        </div>

                        <h3>
                            چیزی پیدا نکردیم
                        </h3>

                        <p>
                            جستجو را تغییر بده یا از پیشنهادهای محبوب شروع کن.
                        </p>

                        <a
                            href="{{ route('salons.discover') }}"
                            class="customer-btn customer-btn-primary"
                        >
                            نمایش همه سالن‌ها
                        </a>

                    </div>

                @endif

            </div>

        </section>


        {{-- ==========================================================
            POPULAR BARBERS
        =========================================================== --}}

        @if($barbers->count())

            <section class="customer-section">

                <div class="customer-container">

                    <div class="customer-section-heading">

                        <div>

                        <span>
                            SPECIALISTS
                        </span>

                            <h2>
                                متخصص‌های محبوب
                            </h2>

                        </div>

                        <a
                            href="{{ route(
                            'salons.discover',
                            ['type' => 'barber']
                        ) }}"
                        >
                            همه متخصص‌ها
                        </a>

                    </div>


                    <div class="horizontal-rail">

                        @foreach($barbers as $barber)

                            @php
                                $barberImage =
                                    $barber->image_path
                                        ? \Illuminate\Support\Facades\Storage::url(
                                            $barber->image_path
                                        )
                                        : null;
                            @endphp


                            <a
                                href="{{ route(
                                'public.salons.show',
                                $barber->salon
                            ) }}"
                                class="specialist-card"
                            >

                                <div class="specialist-avatar">

                                    @if($barberImage)

                                        <img
                                            src="{{ $barberImage }}"
                                            alt="{{ $barber->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        {{ mb_substr(
                                            $barber->name,
                                            0,
                                            1
                                        ) }}

                                    @endif

                                </div>


                                <div class="specialist-info">

                                    <strong>
                                        {{ $barber->name }}
                                    </strong>

                                    <span>
                                    {{ $barber->specialty ?: 'متخصص زیبایی' }}
                                </span>

                                    <small>
                                        {{ $barber->salon?->name }}
                                    </small>

                                </div>


                                <div class="specialist-score">
                                    {{ $barber->completed_bookings_count }}
                                    رزرو
                                </div>

                            </a>

                        @endforeach

                    </div>

                </div>

            </section>

        @endif


        {{-- ==========================================================
            REAL AVAILABILITY
        =========================================================== --}}

        @if($availableSlots->count())

            <section class="customer-section customer-section-soft">

                <div class="customer-container">

                    <div class="customer-section-heading">

                        <div>

                        <span>
                            OPENINGS
                        </span>

                            <h2>
                                همین روزها وقت خالی هست
                            </h2>

                        </div>

                        <span class="section-note">
                        زمان‌های واقعی
                    </span>

                    </div>


                    <div class="availability-grid">

                        @foreach($availableSlots as $item)

                            <a
                                href="{{ route(
                                'public.salons.booking.create',
                                $item['salon']
                            ) }}"
                                class="availability-card"
                            >

                                <div>

                                    <strong>
                                        {{ $item['salon']->name }}
                                    </strong>

                                    <span>
                                    {{ $item['service']->name }}
                                    ·
                                    {{ $item['barber']->name }}
                                </span>

                                </div>


                                <div class="availability-time">

                                <span>
                                    {{ $item['date']->isToday()
                                        ? 'امروز'
                                        : $item['date']->format('Y/m/d')
                                    }}
                                </span>

                                    <strong dir="ltr">
                                        {{ $item['slot']['start'] }}
                                    </strong>

                                </div>

                            </a>

                        @endforeach

                    </div>

                </div>

            </section>

        @endif


        {{-- ==========================================================
            FEATURED SALONS / PORTFOLIO
        =========================================================== --}}

        @if($featuredSalons->count())

            <section class="customer-section">

                <div class="customer-container">

                    <div class="customer-section-heading">

                        <div>

                        <span>
                            FEATURED
                        </span>

                            <h2>
                                سالن‌هایی که ارزش دیدن دارند
                            </h2>

                        </div>

                    </div>


                    <div class="featured-salons">

                        @foreach($featuredSalons as $salon)

                            @php
                                $coverUrl = $salon->cover_path
                                    ? \Illuminate\Support\Facades\Storage::url(
                                        $salon->cover_path
                                    )
                                    : null;
                            @endphp


                            <article class="featured-salon-card">

                                <a
                                    href="{{ route(
                                    'public.salons.show',
                                    $salon
                                ) }}"
                                    class="featured-salon-visual"
                                >

                                    @if($coverUrl)

                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $salon->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <div
                                            class="featured-salon-fallback"
                                        ></div>

                                    @endif

                                </a>


                                <div class="featured-salon-content">

                                    <div>

                                    <span class="section-kicker">
                                        {{ $salon->city ?: 'RM' }}
                                    </span>

                                        <h3>
                                            {{ $salon->name }}
                                        </h3>

                                    </div>


                                    <div class="featured-salon-rating">

                                        <strong>
                                            {{ number_format(
                                                (float) (
                                                    $salon->reviews_avg_rating
                                                    ?? 0
                                                ),
                                                1
                                            ) }}
                                        </strong>

                                        <span>★</span>

                                    </div>


                                    @if($salon->portfolioItems->count())

                                        <div class="portfolio-strip">

                                            @foreach(
                                                $salon->portfolioItems->take(3)
                                                as $portfolio
                                            )

                                                @php
                                                    $image =
                                                        $portfolio->after_image_path
                                                            ? \Illuminate\Support\Facades\Storage::url(
                                                                $portfolio->after_image_path
                                                            )
                                                            : (
                                                                $portfolio->before_image_path
                                                                    ? \Illuminate\Support\Facades\Storage::url(
                                                                        $portfolio->before_image_path
                                                                    )
                                                                    : null
                                                            );
                                                @endphp


                                                @if($image)

                                                    <img
                                                        src="{{ $image }}"
                                                        alt="{{ $portfolio->title }}"
                                                        loading="lazy"
                                                    >

                                                @endif

                                            @endforeach

                                        </div>

                                    @endif


                                    <a
                                        href="{{ route(
                                        'public.salons.booking.create',
                                        $salon
                                    ) }}"
                                        class="customer-btn customer-btn-primary"
                                    >
                                        دیدن سالن و رزرو
                                    </a>

                                </div>

                            </article>

                        @endforeach

                    </div>

                </div>

            </section>

        @endif


        {{-- ==========================================================
            MAP
        =========================================================== --}}

        <section class="customer-section customer-section-soft">

            <div class="customer-container">

                <div class="customer-section-heading">

                    <div>

                    <span>
                        EXPLORE MAP
                    </span>

                        <h2>
                            سالن‌های اطراف تو
                        </h2>

                        <p>
                            موقعیت سالن‌هایی را که روی نقشه ثبت شده‌اند ببین.
                        </p>

                    </div>

                    @if($hasLocation)

                        <span class="section-note">
                        مرتب‌شده بر اساس فاصله
                    </span>

                    @endif

                </div>


                <div
                    id="discover-map"
                    class="discover-map"
                ></div>

            </div>

        </section>


        {{-- ==========================================================
            FINAL CTA
        =========================================================== --}}

        <section class="discover-final-cta">

            <div class="customer-container">

                <div>

                <span>
                    RM
                </span>

                    <h2>
                        نوبت بعدیت رو
                        <strong>همین الان</strong>
                        پیدا کن.
                    </h2>

                    <p>
                        چند انتخاب خوب، چند کلیک و یک نوبت قطعی.
                    </p>

                </div>


                <a
                    href="{{ route('salons.discover') }}"
                    class="customer-btn customer-btn-primary customer-btn-lg"
                >
                    شروع کشف
                    ←
                </a>

            </div>

        </section>

    </div>

@endsection


@push('scripts')

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        crossorigin=""
    ></script>

    @php
        $mapLocations = $mapSalons->map(fn ($salon) => [
            'name' => $salon->name,
            'slug' => $salon->slug,
            'lat' => (float) $salon->latitude,
            'lng' => (float) $salon->longitude,
            'city' => $salon->city,
            'district' => $salon->district,
            'distance' => isset($salon->distance_km)
                ? round((float) $salon->distance_km, 1)
                : null,
        ])->values()->all();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const latitudeInput =
                document.getElementById(
                    'discover-latitude'
                );

            const longitudeInput =
                document.getElementById(
                    'discover-longitude'
                );

            const locationButton =
                document.getElementById(
                    'discover-location'
                );


            if (
                locationButton &&
                navigator.geolocation
            ) {

                locationButton.addEventListener(
                    'click',
                    () => {

                        locationButton.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            (position) => {

                                latitudeInput.value =
                                    position.coords.latitude;

                                longitudeInput.value =
                                    position.coords.longitude;

                                locationButton
                                    .closest('form')
                                    .submit();

                            },
                            () => {

                                locationButton.disabled = false;

                                alert(
                                    'دسترسی به موقعیت مکانی فعال نشد.'
                                );

                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 8000,
                                maximumAge: 300000,
                            }
                        );
                    }
                );
            }


            const mapElement =
                document.getElementById(
                    'discover-map'
                );


            if (
                !mapElement ||
                typeof L === 'undefined'
            ) {
                return;
            }



            const locations = @js($mapLocations);

            let center = [
                35.6892,
                51.3890,
            ];


            @if($hasLocation)

                center = [
                {{ $latitude }},
                {{ $longitude }},
            ];

            @elseif($mapSalons->count())

                center = [
                {{ (float) $mapSalons->first()->latitude }},
                {{ (float) $mapSalons->first()->longitude }},
            ];

            @endif


            const map =
                L.map(
                    mapElement,
                    {
                        scrollWheelZoom: false,
                    }
                ).setView(
                    center,
                    locations.length ? 12 : 10
                );


            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; OpenStreetMap contributors',
                }
            ).addTo(map);


            locations.forEach((location) => {

                const marker =
                    L.marker([
                        location.lat,
                        location.lng,
                    ]).addTo(map);


                const url =
                    `/salons/${location.slug}`;


                marker.bindPopup(`
                <div style="direction:rtl;text-align:right;min-width:180px">
                    <strong>${location.name}</strong>
                    <br>
                    <span>
                        ${location.city || ''}
                        ${location.district || ''}
                    </span>
                    ${
                    location.distance
                        ? `<br><small>${location.distance} km</small>`
                        : ''
                }
                    <br>
                    <a
                        href="${url}"
                        style="display:inline-block;margin-top:8px"
                    >
                        مشاهده سالن
                    </a>
                </div>
            `);

            });


            if (locations.length > 1) {

                const bounds =
                    locations.map(
                        (location) => [
                            location.lat,
                            location.lng,
                        ]
                    );

                map.fitBounds(
                    bounds,
                    {
                        padding: [
                            30,
                            30,
                        ],
                    }
                );
            }

        });
    </script>

@endpush
