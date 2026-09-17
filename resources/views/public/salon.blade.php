@extends('layouts.public')

@section('title', $salon->name . ' | رزرو نوبت آنلاین')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/salon.css') }}">
@endpush

@section('content')

    @php
        $barbers = $salon->barbers ?? collect();
        $services = $salon->services ?? collect();
        $posts = $salon->posts ?? collect();
        $reviews = $salon->reviews ?? collect();

        $todayDow = (now()->dayOfWeek + 1) % 7;

        $todayHours = ($salon->workingHours ?? collect())
            ->where('day_of_week', $todayDow)
            ->where('is_closed', false);

        $isOpenToday = $todayHours->isNotEmpty();

        $postsCountValue = $postsCount ?? $posts->count();
        $reviewsCountValue = $reviewsCount ?? $reviews->count();
        $barbersCountValue = $barbersCount ?? $barbers->count();
        $servicesCountValue = $servicesCount ?? $services->count();

        $rating = $salon->reviews_avg_rating ?? 5;

        $resolveMediaUrl = function (?string $path): ?string {
            return $path
                ? asset('storage/' . ltrim($path, '/'))
                : null;
        };

        $getInitial = function (?string $name): string {
            return mb_substr(trim($name ?: 'م'), 0, 1);
        };
    @endphp


    <div
        class="salon-page"
        id="salonPage"
        data-salon-id="{{ $salon->id }}"
        data-salon-name="{{ $salon->name }}"
        data-is-auth="{{ auth()->check() ? '1' : '0' }}"
        data-login-url="{{ route('login') }}"
        data-availability-url="{{ route('public.salons.booking.availability', $salon) }}"
        data-prepare-url="{{ route('public.salons.booking.prepare', $salon) }}"
        data-csrf="{{ csrf_token() }}"
    >

        {{-- =========================================================
            TOPBAR
        ========================================================== --}}
        <header class="topbar">
            <div class="topbar-in">

                <a
                    href="{{ route('salons.discover') }}"
                    class="back-home"
                    aria-label="بازگشت به کشف سالن‌ها"
                >
                    <span>←</span>
                    <span>کشف سالن‌ها</span>
                </a>


                <a
                    href="{{ url('/') }}"
                    class="brand"
                >
                    <span class="dot">✦</span>
                    <span>{{ $salon->name }}</span>
                </a>


                <nav
                    class="topnav"
                    aria-label="منوی سالن"
                >

                    @if($posts->isNotEmpty())
                        <a href="#gallery">
                            گالری
                        </a>
                    @endif

                    @if($services->isNotEmpty())
                        <a href="#services">
                            خدمات
                        </a>
                    @endif

                    @if($barbers->isNotEmpty())
                        <a href="#team">
                            تیم
                        </a>
                    @endif

                    <a href="#location">
                        آدرس
                    </a>

                    <a href="#about">
                        درباره ما
                    </a>

                    <button
                        type="button"
                        class="btn-diamond"
                        data-open-booking
                    >
                        رزرو نوبت
                    </button>

                </nav>

            </div>
        </header>


        <main class="wrap">

            {{-- =====================================================
                HERO
            ====================================================== --}}
            <section class="hero reveal">

                <div class="cover">

                    @if($salon->cover_path)

                        <img
                            src="{{ $resolveMediaUrl($salon->cover_path) }}"
                            alt="{{ $salon->name }}"
                            loading="eager"
                        >

                    @endif


                    <div class="cover-badge">

                        <span class="pulse-dot {{ $isOpenToday ? '' : 'is-closed' }}"></span>

                        {{ $isOpenToday ? 'الان باز است' : 'امروز تعطیل' }}

                    </div>

                </div>


                <div class="profile-bar">

                    <div class="avatar">

                        @if($salon->logo_path)

                            <img
                                src="{{ $resolveMediaUrl($salon->logo_path) }}"
                                alt="{{ $salon->name }}"
                                loading="lazy"
                            >

                        @else

                            <span>
                                {{ $getInitial($salon->name) }}
                            </span>

                        @endif

                    </div>


                    <div class="profile-info">

                        <div class="name-row">

                            <h1>
                                {{ $salon->name }}
                            </h1>

                            <span class="verified">
                                ✓
                            </span>

                        </div>


                        <div class="tagline">

                            {{ Str::limit(
                                $salon->description ?: 'سالن تخصصی مو و استایل',
                                100
                            ) }}

                        </div>


                        <div class="stats">

                            <div class="stat">

                                <b>
                                    {{ number_format($postsCountValue) }}
                                </b>

                                <span>
                                    نمونه‌کار
                                </span>

                            </div>


                            <div class="stat">

                                <b>
                                    {{ number_format($reviewsCountValue) }}
                                </b>

                                <span>
                                    نظر ثبت‌شده
                                </span>

                            </div>


                            <div class="stat rate">

                                <b>
                                    ★ {{ number_format($rating, 1) }}
                                </b>

                                <span>
                                    امتیاز مشتریان
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="profile-actions">

                        <button
                            type="button"
                            class="btn-diamond"
                            data-open-booking
                        >
                            رزرو نوبت
                        </button>


                        <button
                            type="button"
                            class="btn-ghost"
                            data-share
                        >
                            ↗ اشتراک‌گذاری
                        </button>

                    </div>

                </div>


                @if($salon->description)

                    <p class="bio">
                        {{ $salon->description }}
                    </p>

                @endif


                <div class="chips">

                    <span class="chip diamond">
                        ✦ {{ $barbersCountValue }} آرایشگر
                    </span>

                    <span class="chip">
                        {{ $servicesCountValue }} خدمت
                    </span>

                    @if($isOpenToday)
                        <span class="chip">
                            امروز باز است
                        </span>
                    @else
                        <span class="chip">
                            امروز تعطیل
                        </span>
                    @endif

                </div>

            </section>


            {{-- =====================================================
                BOOKING CTA
            ====================================================== --}}
            <section class="booking-cta reveal">

                <div class="bk-text">

                    <div class="eyebrow">
                        <span>◆</span>
                        نوبت‌دهی آنلاین
                    </div>

                    <h2>
                        وقتت رو همین حالا رزرو کن
                    </h2>

                    <p>
                        آرایشگر و خدمت موردنظرت رو انتخاب کن،
                        ساعت‌های خالی رو ببین و نوبتت رو آنلاین ثبت کن.
                    </p>

                </div>


                <div class="bk-side">

                    <button
                        type="button"
                        class="btn-book"
                        data-open-booking
                    >
                        <span>📅</span>
                        انتخاب زمان و رزرو
                    </button>

                    <div class="hint">
                        سریع، آنلاین و بدون تماس
                    </div>

                </div>

            </section>





            {{-- =====================================================
                GALLERY
            ====================================================== --}}
            @if($posts->isNotEmpty())

                <section
                    class="section reveal"
                    id="gallery"
                >

                    <div class="sec-head">

                        <h3>
                            گالری سالن
                        </h3>

                        <div class="line"></div>

                        <span class="count">
                            {{ number_format($postsCountValue) }} پست
                        </span>

                    </div>


                    <div class="tabs">

                        <button
                            type="button"
                            class="tab active"
                            data-filter="all"
                        >
                            همه
                        </button>


                        <button
                            type="button"
                            class="tab"
                            data-filter="reel"
                        >
                            🎬 ریلز
                        </button>


                        <button
                            type="button"
                            class="tab"
                            data-filter="video"
                        >
                            ▶ ویدیو
                        </button>


                        <button
                            type="button"
                            class="tab"
                            data-filter="photo"
                        >
                            🖼 عکس
                        </button>


                        <button
                            type="button"
                            class="tab"
                            data-filter="gif"
                        >
                            ✨ گیف
                        </button>

                    </div>


                    <div
                        class="gallery"
                        id="galleryGrid"
                    >

                        @foreach($posts->take(12) as $post)

                            @php
                                $type = $post->type instanceof \App\Enums\PostType
                                    ? $post->type->value
                                    : ($post->type ?: 'photo');

                                $mediaUrl = $resolveMediaUrl(
                                    $post->media_path
                                );

                                $thumbnailUrl = $resolveMediaUrl(
                                    $post->thumbnail_path
                                );

                                $typeLabel = match ($type) {
                                    'reel' => 'ریلز',
                                    'video' => 'ویدیو',
                                    'gif' => 'گیف',
                                    default => 'عکس',
                                };
                            @endphp


                            <article
                                class="tile"
                                data-type="{{ $type }}"
                            >

                                <div class="media">

                                    @if(
                                        $mediaUrl &&
                                        in_array(
                                            $type,
                                            ['photo', 'gif'],
                                            true
                                        )
                                    )

                                        <img
                                            src="{{ $mediaUrl }}"
                                            alt="{{ $post->title ?: $salon->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >

                                    @elseif(
                                        $mediaUrl &&
                                        in_array(
                                            $type,
                                            ['video', 'reel'],
                                            true
                                        )
                                    )

                                        <video
                                            src="{{ $mediaUrl }}"
                                            @if($thumbnailUrl)
                                            poster="{{ $thumbnailUrl }}"
                                            @endif
                                            muted
                                            playsinline
                                            preload="metadata"
                                        ></video>

                                    @else

                                        <div class="media-empty">
                                            <span>✦</span>
                                        </div>

                                    @endif

                                </div>


                                <div class="ov"></div>


                                <span class="badge-type {{ $type }}">
                                    {{ $typeLabel }}
                                </span>


                                @if(in_array($type, ['video', 'reel'], true))

                                    <div class="play">
                                        ▶
                                    </div>

                                @endif


                                <div class="meta">

                                    @if($post->barber)

                                        <span class="views">
                                            ✂ {{ $post->barber->name }}
                                        </span>

                                    @elseif($post->service)

                                        <span class="views">
                                            ✦ {{ $post->service->name }}
                                        </span>

                                    @elseif($post->title)

                                        <span class="views">
                                            {{ $post->title }}
                                        </span>

                                    @endif

                                </div>

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif
            {{-- =====================================================
                SERVICES
            ====================================================== --}}
            @if($services->isNotEmpty())

                <section
                    class="section reveal"
                    id="services"
                >

                    <div class="sec-head">

                        <h3>
                            خدمات سالن
                        </h3>

                        <div class="line"></div>

                        <span class="count">
                            {{ number_format($servicesCountValue) }} خدمت
                        </span>

                    </div>


                    <div class="services-grid">

                        @foreach($services as $service)

                            @php
                                $serviceImage = $service->image_path
                                    ? $resolveMediaUrl($service->image_path)
                                    : null;
                            @endphp


                            <article class="service-card">

                                <div class="service-media">

                                    @if($serviceImage)

                                        <img
                                            src="{{ $serviceImage }}"
                                            alt="{{ $service->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >

                                    @else

                                        <div class="service-empty">
                                            ✦
                                        </div>

                                    @endif

                                </div>


                                <div class="service-content">

                                    <div class="service-title">
                                        {{ $service->name }}
                                    </div>


                                    @if($service->description)

                                        <div class="service-description">

                                            {{ Str::limit(
                                                $service->description,
                                                110
                                            ) }}

                                        </div>

                                    @endif


                                    <div class="service-info">

                                        <div class="service-price">

                                            @if($service->price)
                                                {{ number_format($service->price) }}
                                                تومان
                                            @else
                                                توافقی
                                            @endif

                                        </div>


                                        <div class="service-duration">

                                            {{ $service->duration_minutes }}
                                            دقیقه

                                        </div>

                                    </div>


                                    <button
                                        type="button"
                                        class="service-book"
                                        data-open-booking
                                        data-service-id="{{ $service->id }}"
                                    >
                                        انتخاب این خدمت
                                        <span>←</span>
                                    </button>

                                </div>

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif

            {{-- =====================================================
                REVIEWS
            ====================================================== --}}
            @if($reviews->isNotEmpty())

                <section class="section reveal">

                    <div class="sec-head">

                        <h3>
                            نظر مشتریان
                        </h3>

                        <div class="line"></div>

                        <span class="count">
                            {{ number_format($reviewsCountValue) }} نظر
                        </span>

                    </div>


                    <div class="comments">

                        @foreach($reviews->take(6) as $review)

                            @php
                                $customer = $review->customer;

                                $avatarUrl = $customer?->avatar
                                    ? $resolveMediaUrl($customer->avatar)
                                    : null;

                                $ratingValue = max(
                                    0,
                                    min(
                                        5,
                                        (int) $review->rating
                                    )
                                );
                            @endphp


                            <article class="comment">

                                <div class="c-top">

                                    <div class="c-av">

                                        @if($avatarUrl)

                                            <img
                                                src="{{ $avatarUrl }}"
                                                alt="{{ $customer?->name ?? 'مشتری' }}"
                                                loading="lazy"
                                            >

                                        @else

                                            {{ $getInitial($customer?->name) }}

                                        @endif

                                    </div>


                                    <div>

                                        <div class="c-name">
                                            {{ $customer?->name ?? 'مشتری' }}
                                        </div>

                                        <div class="c-date">
                                            {{ $review->created_at?->diffForHumans() }}
                                        </div>

                                    </div>


                                    <div class="c-stars">

                                        <span>
                                            {{ str_repeat('★', $ratingValue) }}
                                        </span>

                                        <span class="muted-stars">
                                            {{ str_repeat('★', 5 - $ratingValue) }}
                                        </span>

                                    </div>

                                </div>


                                @if($review->comment)

                                    <p class="c-body">
                                        {{ $review->comment }}
                                    </p>

                                @endif


                                @if($review->booking?->service)

                                    <div class="c-service">
                                        خدمت:
                                        {{ $review->booking->service->name }}
                                    </div>

                                @endif

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif


            {{-- =====================================================
                TEAM
            ====================================================== --}}
            @if($barbers->isNotEmpty())

                <section
                    class="section reveal"
                    id="team"
                >

                    <div class="sec-head">

                        <h3>
                            تیم آرایشگران
                        </h3>

                        <div class="line"></div>

                        <span class="count">
                            {{ number_format($barbersCountValue) }} متخصص
                        </span>

                    </div>


                    <div class="team-grid">

                        @foreach($barbers as $barber)

                            @php
                                $barberReviews = $reviews->filter(
                                    fn ($review) =>
                                        ($review->booking?->barber_id ?? null)
                                        === $barber->id
                                );

                                $barberRating = $barberReviews->isNotEmpty()
                                    ? round($barberReviews->avg('rating'), 1)
                                    : 5;

                                $barberImage = $barber->image_path
                                    ? $resolveMediaUrl($barber->image_path)
                                    : null;
                            @endphp


                            <article class="tcard">

                                <div class="t-head">

                                    <div class="t-av">

                                        @if($barberImage)

                                            <img
                                                src="{{ $barberImage }}"
                                                alt="{{ $barber->name }}"
                                                loading="lazy"
                                            >

                                        @else

                                            {{ $getInitial($barber->name) }}

                                        @endif

                                    </div>


                                    <div>

                                        <div class="t-name">
                                            {{ $barber->name }}
                                        </div>

                                        <div class="t-role">
                                            {{ $barber->specialty ?: 'آرایشگر' }}
                                        </div>

                                    </div>


                                    <div class="t-rating">

                                        <b>
                                            {{ number_format($barberRating, 1) }}
                                        </b>

                                        <span>
                                            {{ number_format($barberReviews->count()) }}
                                            نظر
                                        </span>

                                    </div>

                                </div>


                                @if($barber->bio)

                                    <p class="t-bio">
                                        {{ Str::limit(
                                            $barber->bio,
                                            130
                                        ) }}
                                    </p>

                                @endif


                                <div class="t-foot">

                                    <button
                                        type="button"
                                        class="btn-diamond"
                                        data-open-booking
                                        data-barber-id="{{ $barber->id }}"
                                    >
                                        رزرو با
                                        {{ Str::before($barber->name, ' ') ?: $barber->name }}
                                    </button>

                                </div>

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif


            {{-- =====================================================
                LOCATION + ABOUT
            ====================================================== --}}
            <section
                class="section reveal"
                id="location"
            >

                <div class="sec-head">

                    <h3>
                        آدرس و درباره ما
                    </h3>

                    <div class="line"></div>

                </div>


                <div class="bottom-grid">

                    {{-- MAP --}}
                    <div class="map-card">

                        @if($mapsEmbedUrl)

                            <iframe
                                src="{{ $mapsEmbedUrl }}"
                                loading="lazy"
                                title="موقعیت {{ $salon->name }}"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                            ></iframe>


                            <div class="map-overlay">

                                <div class="map-addr">

                                    <b>
                                        {{ $salon->name }}
                                    </b>

                                    @if($fullAddress)

                                        <span>
                                            {{ $fullAddress }}
                                        </span>

                                    @endif

                                </div>


                                @if($googleMapsUrl)

                                    <a
                                        href="{{ $googleMapsUrl }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn-ghost"
                                    >
                                        مسیریابی ↗
                                    </a>

                                @endif

                            </div>

                        @else

                            <div class="map-empty">
                                موقعیت مکانی ثبت نشده
                            </div>

                        @endif

                    </div>


                    {{-- ABOUT --}}
                    <div
                        class="about-card"
                        id="about"
                    >

                        <div class="sub">
                            درباره ما
                        </div>


                        <h3>
                            {{ $salon->name }}
                        </h3>


                        <p>
                            {{ $salon->description ?: 'ما به وقت تو احترام می‌ذاریم، به سلیقه‌ت گوش می‌دیم و با ابزار و محصولات حرفه‌ای کار می‌کنیم.' }}
                        </p>


                        <div class="feat">

                            <div>
                                <i>✂️</i>
                                تجهیزات استریل
                            </div>

                            <div>
                                <i>⏱</i>
                                وقت‌شناسی دقیق
                            </div>

                            <div>
                                <i>💎</i>
                                محصولات اورجینال
                            </div>

                            <div>
                                <i>🅿️</i>
                                پارکینگ
                            </div>

                        </div>


                        <div class="hours">

                            <span class="hours-today">

                                @if($todayHours->isNotEmpty())

                                    امروز:
                                    {{ $todayHours
                                        ->pluck('start_time')
                                        ->map(fn ($time) => substr($time, 0, 5))
                                        ->join(' | ')
                                    }}

                                @else

                                    امروز: تعطیل

                                @endif

                            </span>


                            <span class="open {{ $isOpenToday ? '' : 'is-closed' }}">

                                <span class="pulse-dot {{ $isOpenToday ? '' : 'is-closed' }}"></span>

                                {{ $isOpenToday ? 'الان باز' : 'بسته' }}

                            </span>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                RELATED SALONS
            ====================================================== --}}
            @if($relatedSalons->isNotEmpty())

                <section class="section reveal">

                    <div class="sec-head">

                        <h3>
                            سالن‌های مشابه
                        </h3>

                        <div class="line"></div>

                    </div>


                    <div class="related-grid">

                        @foreach($relatedSalons as $related)

                            @php
                                $relatedLogo = $related->logo_path
                                    ? $resolveMediaUrl($related->logo_path)
                                    : null;
                            @endphp


                            <a
                                href="{{ route('public.salons.show', $related) }}"
                                class="related-card"
                            >

                                <div class="related-logo">

                                    @if($relatedLogo)

                                        <img
                                            src="{{ $relatedLogo }}"
                                            alt="{{ $related->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <span>
                                            {{ $getInitial($related->name) }}
                                        </span>

                                    @endif

                                </div>


                                <div class="related-name">
                                    {{ $related->name }}
                                </div>


                                <div class="related-meta">

                                    <span>
                                        ★ {{ number_format(
                                            $related->reviews_avg_rating ?? 5,
                                            1
                                        ) }}
                                    </span>


                                    @if(($related->services_count ?? 0) > 0)

                                        <span>
                                            {{ $related->services_count }}
                                            خدمت
                                        </span>

                                    @endif

                                </div>

                            </a>

                        @endforeach

                    </div>

                </section>

            @endif


            <div class="page-spacer"></div>

        </main>


        {{-- =========================================================
            MOBILE BOOKING
        ========================================================== --}}
        <div class="float-book">

            <button
                type="button"
                data-open-booking
            >
                📅 رزرو نوبت
            </button>

        </div>


        {{-- =========================================================
            BOOKING MODAL
        ========================================================== --}}
        <div
            class="modal-back"
            id="bookingModal"
            aria-hidden="true"
        >

            <div class="modal">

                <button
                    type="button"
                    class="modal-close"
                    data-close-booking
                    aria-label="بستن"
                >
                    ✕
                </button>


                <div id="modalMain">

                    <div class="m-head">

                        <div class="eyebrow">
                            ◆ رزرو نوبت
                        </div>

                        <h2>
                            زمانت رو انتخاب کن
                        </h2>

                        <p>
                            آرایشگر و خدمت رو انتخاب کن،
                            بعد تقویم زنده سالن رو ببین.
                        </p>

                    </div>


                    <div class="m-filters">

                        <label class="field">

                            <span>
                                آرایشگر
                            </span>

                            <select id="filterBarber">

                                @foreach($barbers as $barber)

                                    <option value="{{ $barber->id }}">
                                        {{ $barber->name }}

                                        @if($barber->specialty)
                                            — {{ $barber->specialty }}
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                        </label>


                        <label class="field">

                            <span>
                                خدمت
                            </span>

                            <select id="filterService">

                                @foreach($services as $service)

                                    <option value="{{ $service->id }}">
                                        {{ $service->name }}

                                        ({{ $service->duration_minutes }} دقیقه

                                        @if($service->price)
                                            — {{ number_format($service->price) }} تومان
                                        @endif

                                        )
                                    </option>

                                @endforeach

                            </select>

                        </label>

                    </div>


                    <div class="m-grid">

                        <div>

                            <div class="cal-head">

                                <b id="calTitle">
                                    —
                                </b>

                                <div class="cal-nav">

                                    <button
                                        type="button"
                                        data-cal-shift="1"
                                        aria-label="ماه بعد"
                                    >
                                        ‹
                                    </button>

                                    <button
                                        type="button"
                                        data-cal-shift="-1"
                                        aria-label="ماه قبل"
                                    >
                                        ›
                                    </button>

                                </div>

                            </div>


                            <div class="cal-week">

                                <span>ش</span>
                                <span>ی</span>
                                <span>د</span>
                                <span>س</span>
                                <span>چ</span>
                                <span>پ</span>
                                <span>ج</span>

                            </div>


                            <div
                                class="cal-days"
                                id="calDays"
                            ></div>


                            <div class="legend">

                                <i>
                                    <span class="sq selected"></span>
                                    انتخاب‌شده
                                </i>

                                <i>
                                    <span class="sq available"></span>
                                    موجود
                                </i>

                                <i>
                                    <span class="sq past"></span>
                                    گذشته
                                </i>

                            </div>

                        </div>


                        <div>

                            <div class="slots-title">

                                <span>
                                    ساعت‌های خالی
                                </span>

                                <small id="slotDate">
                                    — یک روز انتخاب کن
                                </small>

                            </div>


                            <div
                                class="slots"
                                id="slots"
                            >
                                <div class="slots-msg">
                                    اول از تقویم یک روز انتخاب کن
                                </div>
                            </div>

                        </div>

                    </div>


                    <div class="m-foot">

                        <div class="m-summary">

                            <div class="row">

                                <span>
                                    آرایشگر
                                </span>

                                <b id="sumBarber">
                                    —
                                </b>

                            </div>


                            <div class="row">

                                <span>
                                    خدمت
                                </span>

                                <b id="sumService">
                                    —
                                </b>

                            </div>


                            <div class="row">

                                <span>
                                    زمان
                                </span>

                                <b id="sumTime">
                                    —
                                </b>

                            </div>

                        </div>


                        <button
                            type="button"
                            class="btn-confirm"
                            id="confirmBtn"
                            disabled
                        >
                            تأیید رزرو
                        </button>

                    </div>

                </div>


                <div
                    class="success"
                    id="successBox"
                    aria-hidden="true"
                >

                    <div class="tick">
                        ✓
                    </div>

                    <h3>
                        نوبتت ثبت شد!
                    </h3>

                    <p id="successText">
                        —
                    </p>

                    <button
                        type="button"
                        class="btn-diamond success-close"
                        data-close-booking
                    >
                        باشه، ممنون
                    </button>

                </div>

            </div>

        </div>


        {{-- =========================================================
            FOOTER
        ========================================================== --}}
        <footer>

            ساخته‌شده با
            <b>✦</b>
            برای
            <b>{{ $salon->name }}</b>

            — تمام حقوق محفوظ است.

        </footer>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/customer.js') }}" defer></script>
@endpush
