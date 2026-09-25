@extends('layouts.customer')

@section('customer_shell', 'standalone')

@push('head')
    @vite('resources/css/salon.css')
@endpush

@push('scripts')
    @vite('resources/js/salon.js')
@endpush

@section('title', $salon->name . ' | رزرو نوبت آنلاین')

@section(
    'meta_description',
    Str::limit(
        $salon->description
            ?: 'پروفایل ' . $salon->name . '؛ خدمات، نمونه‌کارها، تیم و رزرو نوبت آنلاین.',
        155
    )
)

@section('content')

    @php
        use App\Enums\PostType;
        use Illuminate\Support\Facades\Storage;

        $barbers = $salon->barbers ?? collect();
        $services = $salon->services ?? collect();
        $posts = $salon->posts ?? collect();
        $reviews = $salon->reviews ?? collect();
        $relatedSalons = $relatedSalons ?? collect();

        /*
        |--------------------------------------------------------------------------
        | Media URL
        |--------------------------------------------------------------------------
        */

        $resolveMediaUrl = function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            if (
                str_starts_with($path, 'http://') ||
                str_starts_with($path, 'https://') ||
                str_starts_with($path, '/')
            ) {
                return $path;
            }

            return Storage::disk('public')->url($path);
        };

        /*
        |--------------------------------------------------------------------------
        | Initial
        |--------------------------------------------------------------------------
        */

        $getInitial = function (?string $name): string {
            return mb_substr(
                trim($name ?: 'ن'),
                0,
                1
            );
        };

        /*
        |--------------------------------------------------------------------------
        | Post type normalization
        |--------------------------------------------------------------------------
        |
        | Backward compatibility:
        | old "photo" -> current enum value "image"
        |
        */

        $normalizePostType = function ($post): string {
            $type = $post->type instanceof PostType
                ? $post->type->value
                : (string) ($post->type ?? '');

            if ($type === 'photo') {
                return 'image';
            }

            return $type ?: 'image';
        };

        /*
        |--------------------------------------------------------------------------
        | Post counts
        |--------------------------------------------------------------------------
        */

        $postTypeCounts = [
            'all' => $postsCount,
            'reel' => 0,
            'video' => 0,
            'image' => 0,
            'gif' => 0,
        ];

        foreach ($posts as $post) {
            $postType = $normalizePostType($post);

            if (isset($postTypeCounts[$postType])) {
                $postTypeCounts[$postType]++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Rating
        |--------------------------------------------------------------------------
        */

        $rating = $salon->reviews_avg_rating;

        /*
        |--------------------------------------------------------------------------
        | Booking availability
        |--------------------------------------------------------------------------
        |
        | The controller is the single source of truth for today's schedule.
        | It already applies the app timezone and today's explicit close status.
        |--------------------------------------------------------------------------
        */

        $todayHours ??= collect();
        $todayHoursText ??= 'امروز ساعات کاری ثبت نشده';
        $statusText ??= 'امروز وضعیت سالن مشخص نشده';
        $isOpenToday = (bool) ($isOpenToday ?? false);
        $isOpenNow = (bool) ($isOpenNow ?? false);

        $bookingEnabled =
            $barbers->isNotEmpty() &&
            $services->isNotEmpty();
    @endphp

    <div
        class="salon-page"
        id="salonPage"
        style="--salon-brand-primary: {{ $salon->primary_color ?: '#6757E8' }}; --salon-brand-secondary: {{ $salon->secondary_color ?: '#37B8C8' }};"
        data-salon-id="{{ $salon->id }}"
        data-salon-name="{{ $salon->name }}"
        data-is-auth="{{ auth()->check() ? '1' : '0' }}"
        data-login-url="{{ route('login', ['entry' => 'customer']) }}"
        data-availability-url="{{ route('public.salons.booking.availability', $salon) }}"
        data-prepare-url="{{ route('public.salons.booking.prepare', $salon) }}"
        data-csrf="{{ csrf_token() }}"
        data-booking-enabled="{{ $bookingEnabled ? '1' : '0' }}"
        data-today="{{ now(config('app.timezone', 'Asia/Tehran'))->toDateString() }}"
        dir="rtl"
    >

        {{-- ==========================================================
            TOPBAR
        =========================================================== --}}

        <header class="topbar">
            <div class="topbar-in">

                <a
                    href="{{ route('salons.discover') }}"
                    class="back-home"
                    aria-label="بازگشت به کشف سالن‌ها"
                >
                    <span class="back-icon">←</span>
                    <span class="back-label">کشف سالن‌ها</span>
                </a>


                <a
                    href="{{ url('/') }}"
                    class="brand"
                    aria-label="NOBAT"
                >
                    <span class="brand-mark">N</span>

                    <span class="brand-copy">
                        <strong>NOBAT</strong>
                        <small>{{ $salon->name }}</small>
                    </span>
                </a>


                <nav
                    class="topnav"
                    aria-label="منوی سالن"
                >
                    @if($services->isNotEmpty())
                        <a href="#services">خدمات</a>
                    @endif

                    @if($posts->isNotEmpty())
                        <a href="#gallery">گالری</a>
                    @endif

                    @if($barbers->isNotEmpty())
                        <a href="#team">تیم</a>
                    @endif

                    <a href="#location">موقعیت</a>

                    <a href="#about">درباره</a>

                    @if($bookingEnabled)
                        <button
                            type="button"
                            class="btn-diamond btn-top-book"
                            data-open-booking
                        >
                            رزرو نوبت
                        </button>
                    @endif
                </nav>

            </div>


            <div class="mobile-anchor-bar">
                @if($bookingEnabled)
                    <button
                        type="button"
                        class="mobile-book-anchor"
                        data-open-booking
                    >
                        رزرو
                    </button>
                @endif

                @if($services->isNotEmpty())
                    <a href="#services">خدمات</a>
                @endif

                @if($posts->isNotEmpty())
                    <a href="#gallery">گالری</a>
                @endif

                @if($barbers->isNotEmpty())
                    <a href="#team">تیم</a>
                @endif

                <a href="#location">موقعیت</a>

                <a href="#about">درباره</a>
            </div>
        </header>


        <main class="wrap">

            {{-- =======================================================
                HERO
            ======================================================== --}}

            <section class="hero reveal">

                <div class="cover">

                    @if($salon->cover_path)

                        <img
                            src="{{ $resolveMediaUrl($salon->cover_path) }}"
                            alt="{{ $salon->name }}"
                            loading="eager"
                            fetchpriority="high"
                        >

                    @else

                        <div class="cover-fallback">
                            <span>NOBAT</span>
                            <small>{{ $salon->name }}</small>
                        </div>

                    @endif


                    <div class="cover-shade"></div>

                    <div class="cover-topline">
                        <span class="cover-label">
                            SALON PROFILE
                        </span>

                        <span class="cover-location">
                            {{ collect([
                                $salon->district,
                                $salon->city
                            ])->filter()->implode('، ') ?: 'ایران' }}
                        </span>
                    </div>


                    <div class="cover-status">
                        <span
                            class="pulse-dot {{ $isOpenNow ? '' : 'is-closed' }}"
                        ></span>

                        {{ $statusText }}
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

                            <span class="verified verified-live">
                                <span></span>
                                سالن فعال
                            </span>

                        </div>


                        <p class="tagline">
                            {{
                                Str::limit(
                                    $salon->description
                                        ?: 'سالن تخصصی زیبایی، مو و استایل',
                                    130
                                )
                            }}
                        </p>


                        <div class="stats">

                            <div class="stat">
                                <b>
                                    {{ number_format($postsCount) }}
                                </b>
                                <span>نمونه‌کار</span>
                            </div>

                            <div class="stat">
                                <b>
                                    {{ number_format($servicesCount) }}
                                </b>
                                <span>خدمت</span>
                            </div>

                            <div class="stat">
                                <b>
                                    {{ number_format($barbersCount) }}
                                </b>
                                <span>متخصص</span>
                            </div>

                            <div class="stat rate">
                                <b>
                                    @if($rating !== null)
                                        ★ {{ number_format($rating, 1) }}
                                    @else
                                        —
                                    @endif
                                </b>
                                <span>امتیاز</span>
                            </div>

                        </div>

                    </div>


                    <div class="profile-actions">

                        @if($bookingEnabled)

                            <button
                                type="button"
                                class="btn-diamond"
                                data-open-booking
                            >
                                رزرو نوبت
                            </button>

                        @endif

                        <button
                            type="button"
                            class="btn-ghost"
                            data-share
                        >
                            ↗ اشتراک‌گذاری
                        </button>

                    </div>

                </div>


                <div class="hero-bottom">

                    <div class="hero-bio">
                        @if($salon->description)
                            {{ $salon->description }}
                        @else
                            فضای حرفه‌ای، تیم متخصص و خدمات زیبایی با امکان
                            نوبت‌دهی آنلاین.
                        @endif
                    </div>


                    <div class="hero-meta">

                        <span>
                            <i>⌖</i>
                            {{
                                collect([
                                    $salon->district,
                                    $salon->city,
                                    $salon->province
                                ])->filter()->implode('، ')
                                ?: 'موقعیت ثبت نشده'
                            }}
                        </span>

                        <span>
                            <i>◷</i>
                            {{
                                $todayHoursText
                                    ?: 'امروز ساعات کاری ثبت نشده'
                            }}
                        </span>

                    </div>

                </div>


                <div class="chips">
<span class="chip">
                        {{ number_format($barbersCount) }} متخصص
                    </span>

                    <span class="chip">
                        {{ number_format($servicesCount) }} خدمت
                    </span>
</div>

            </section>


            {{-- =======================================================
                BOOKING CTA
            ======================================================== --}}

            @if($bookingEnabled)

                <section class="booking-cta reveal">

                    <div class="booking-cta-glow"></div>

                    <div class="bk-text">

                        <div class="eyebrow">
                            <span>◆</span>
                            نوبت‌دهی آنلاین
                        </div>

                        <h2>
                            وقتت را همین حالا انتخاب کن
                        </h2>

                        <p>
                            خدمت و متخصص را انتخاب کن، زمان‌های خالی را ببین
                            و بدون تماس تلفنی نوبتت را ثبت کن.
                        </p>

                    </div>


                    <div class="bk-side">

                        <button
                            type="button"
                            class="btn-book"
                            data-open-booking
                        >
                            <span class="btn-book-icon">◷</span>

                            <span>
                                انتخاب زمان و رزرو
                            </span>

                            <span class="btn-arrow">
                                ←
                            </span>
                        </button>

                        <div class="hint">
                            مشاهده ظرفیت‌های واقعی سالن
                        </div>

                    </div>

                </section>

            @endif


            {{-- =======================================================
                GALLERY
            ======================================================== --}}

            @if($posts->isNotEmpty())

                <section
                    class="section reveal gallery-section"
                    id="gallery"
                >

                    <div class="section-topline">

                        <div>

                            <span class="section-kicker">
                                02 — WORK
                            </span>

                            <h2>
                                نمونه‌کارها
                            </h2>

                            <p>
                                بخشی از کارهای اخیر {{ $salon->name }}
                            </p>

                        </div>


                        <div class="section-count">
                            {{ number_format($postsCount) }}
                            محتوا
                        </div>

                    </div>


                    <div
                        class="tabs"
                        role="tablist"
                        aria-label="فیلتر نمونه‌کارها"
                    >

                        <button
                            type="button"
                            class="tab active"
                            data-filter="all"
                            role="tab"
                            aria-selected="true"
                        >
                            همه
                            <span>{{ number_format($postsCount) }}</span>
                        </button>


                        @if($postTypeCounts['reel'] > 0)
                            <button
                                type="button"
                                class="tab"
                                data-filter="reel"
                                role="tab"
                                aria-selected="false"
                            >
                                ریلز
                                <span>{{ number_format($postTypeCounts['reel']) }}</span>
                            </button>
                        @endif

                        @if($postTypeCounts['video'] > 0)
                            <button
                                type="button"
                                class="tab"
                                data-filter="video"
                                role="tab"
                                aria-selected="false"
                            >
                                ویدیو
                                <span>{{ number_format($postTypeCounts['video']) }}</span>
                            </button>
                        @endif

                        @if($postTypeCounts['image'] > 0)
                            <button
                                type="button"
                                class="tab"
                                data-filter="image"
                                role="tab"
                                aria-selected="false"
                            >
                                عکس
                                <span>{{ number_format($postTypeCounts['image']) }}</span>
                            </button>
                        @endif

                        @if($postTypeCounts['gif'] > 0)
                            <button
                                type="button"
                                class="tab"
                                data-filter="gif"
                                role="tab"
                                aria-selected="false"
                            >
                                GIF
                                <span>{{ number_format($postTypeCounts['gif']) }}</span>
                            </button>
                        @endif

                    </div>


                    <div
                        class="gallery"
                        id="galleryGrid"
                    >

                        @foreach($posts as $index => $post)

                            @php
                                $type = $normalizePostType($post);

                                $mediaUrl = $resolveMediaUrl(
                                    $post->media_path
                                );

                                $thumbnailUrl = $resolveMediaUrl(
                                    $post->thumbnail_path
                                );

                                $typeLabel = match ($type) {
                                    'reel' => 'ریلز',
                                    'video' => 'ویدیو',
                                    'gif' => 'GIF',
                                    default => 'عکس',
                                };

                                $mediaMeta =
                                    $post->barber?->name
                                    ?: $post->service?->name
                                    ?: $post->title
                                    ?: 'نمونه‌کار سالن';
                            @endphp


                            <article
                                class="
                                    tile
                                    tile-{{ min($index + 1, 6) }}
                                    "
                                data-gallery-item
                                data-type="{{ $type }}"
                                data-src="{{ $mediaUrl }}"
                                data-poster="{{ $thumbnailUrl }}"
                                data-title="{{ $post->title ?: 'نمونه‌کار ' . $salon->name }}"
                                data-caption="{{ $post->caption ?: '' }}"
                                data-meta="{{ $mediaMeta }}"
                                tabindex="0"
                                role="button"
                                aria-label="مشاهده {{ $typeLabel }}"
                            >

                                <div class="media">

                                    @if(
                                        $mediaUrl &&
                                        in_array(
                                            $type,
                                            ['image', 'gif'],
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
                                            loop
                                            preload="none"
                                            aria-label="{{ $post->title ?: $typeLabel }}"
                                        ></video>

                                    @else

                                        <div class="media-empty">
                                            <span>✦</span>
                                        </div>

                                    @endif

                                </div>


                                <div class="tile-gradient"></div>


                                <div class="tile-top">

                                    <span class="badge-type {{ $type }}">
                                        {{ $typeLabel }}
                                    </span>

                                    @if(
                                        in_array(
                                            $type,
                                            ['video', 'reel'],
                                            true
                                        )
                                    )

                                        <span class="tile-play">
                                            ▶
                                        </span>

                                    @endif

                                </div>


                                <div class="tile-bottom">

                                    <strong>
                                        {{ $mediaMeta }}
                                    </strong>

                                    @if($post->caption)

                                        <span>
                                            {{ Str::limit($post->caption, 65) }}
                                        </span>

                                    @endif

                                </div>

                            </article>

                        @endforeach

                    </div>


                    <div
                        class="gallery-empty"
                        id="galleryEmpty"
                        hidden
                    >
                        <div class="gallery-empty-icon">
                            ✦
                        </div>

                        <strong>
                            محتوایی در این دسته وجود ندارد
                        </strong>

                        <span>
                            یک دسته دیگر را امتحان کن.
                        </span>
                    </div>

                </section>

            @endif


            {{-- =======================================================
                SERVICES
            ======================================================== --}}

            @if($services->isNotEmpty())

                <section
                    class="section reveal"
                    id="services"
                >

                    <div class="section-topline">

                        <div>

                            <span class="section-kicker">
                                03 — SERVICES
                            </span>

                            <h2>
                                خدمات سالن
                            </h2>

                            <p>
                                خدماتی که می‌توانی همین حالا برایشان نوبت بگیری.
                            </p>

                        </div>

                        <div class="section-count">
                            {{ number_format($servicesCount) }}
                            خدمت
                        </div>

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

                                        <div class="service-media-fallback">
                                            <span>✦</span>
                                        </div>

                                    @endif

                                    <div class="service-media-overlay"></div>

                                    <span class="service-number">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                </div>


                                <div class="service-content">

                                    <div class="service-title">
                                        {{ $service->name }}
                                    </div>


                                    @if($service->description)

                                        <p class="service-description">
                                            {{ Str::limit($service->description, 105) }}
                                        </p>

                                    @endif


                                    <div class="service-info">

                                        <strong class="service-price">

                                            @if(
                                                $service->price !== null &&
                                                (float) $service->price > 0
                                            )
                                                {{ number_format($service->price) }}
                                                <small>تومان</small>
                                            @else
                                                توافقی
                                            @endif

                                        </strong>


                                        <span class="service-duration">
                                            ◷
                                            {{ $service->duration_minutes }}
                                            دقیقه
                                        </span>

                                    </div>


                                    @if($bookingEnabled)

                                        <button
                                            type="button"
                                            class="service-book"
                                            data-open-booking
                                            data-service-id="{{ $service->id }}"
                                        >
                                            <span>
                                                انتخاب این خدمت
                                            </span>

                                            <span>
                                                ←
                                            </span>
                                        </button>

                                    @endif

                                </div>

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif





            {{-- =======================================================
                REVIEWS
            ======================================================== --}}

            <section class="section reveal reviews-section">

                <div class="section-topline">

                    <div>

                        <span class="section-kicker">
                            04 — REVIEWS
                        </span>

                        <h2>
                            تجربه مشتریان
                        </h2>

                        <p>
                            نظرهایی که مشتریان درباره این سالن ثبت کرده‌اند.
                        </p>

                    </div>


                    <div class="rating-summary">

                        <div class="rating-score">

                            @if($rating !== null)
                                {{ number_format($rating, 1) }}
                            @else
                                —
                            @endif

                        </div>

                        <div>

                            <div class="rating-stars">
                                @if($rating !== null)
                                    ★★★★★
                                @else
                                    ☆☆☆☆☆
                                @endif
                            </div>

                            <span>
                                {{ number_format($reviewsCount) }}
                                نظر منتشرشده
                            </span>

                        </div>

                    </div>

                </div>


                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[10px] leading-6 text-content-muted">
                        همین حالا هم می‌توانی به این سالن امتیاز بدهی.
                    </p>

                    @auth
                        @if(auth()->user()->isCustomer())
                            <a
                                href="{{ route('customer.salons.review.create', $salon) }}"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-border bg-surface px-4 text-[10px] font-black text-content transition hover:-translate-y-0.5 hover:border-accent-400 hover:text-accent-700"
                            >
                                ثبت امتیاز به این سالن
                                <span class="mr-2" aria-hidden="true">★</span>
                            </a>
                        @else
                            <span class="text-[10px] font-bold text-content-faint">
                                امتیازدهی برای حساب مشتری فعال است.
                            </span>
                        @endif
                    @else
                        <a
                            href="{{ route('login', ['entry' => 'customer']) }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-xl border border-border bg-surface px-4 text-[10px] font-black text-content transition hover:-translate-y-0.5 hover:border-accent-400 hover:text-accent-700"
                        >
                            ورود برای امتیاز دادن
                            <span class="mr-2" aria-hidden="true">★</span>
                        </a>
                    @endauth
                </div>


                @if($reviews->isNotEmpty())

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


                                    <div class="c-identity">

                                        <strong class="c-name">
                                            {{ $customer?->name ?? 'مشتری' }}
                                        </strong>

                                        <span class="c-date">
                                            {{ $review->created_at?->diffForHumans() }}
                                        </span>

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

                @else

                    <div class="section-empty">
                        هنوز نظری برای نمایش ثبت نشده است.
                    </div>

                @endif

            </section>


            {{-- =======================================================
                TEAM
            ======================================================== --}}

            @if($barbers->isNotEmpty())

                <section
                    class="section reveal"
                    id="team"
                >

                    <div class="section-topline">

                        <div>

                            <span class="section-kicker">
                                05 — TEAM
                            </span>

                            <h2>
                                تیم سالن
                            </h2>

                            <p>
                                متخصصانی که می‌توانی برای رزرو انتخابشان کنی.
                            </p>

                        </div>

                        <div class="section-count">
                            {{ number_format($barbersCount) }}
                            متخصص
                        </div>

                    </div>


                    <div class="team-grid">

                        @foreach($barbers as $barber)

                            @php
                                $barberReviews = $reviews->filter(
                                    fn ($review) =>
                                        (int) ($review->booking?->barber_id ?? 0)
                                        === (int) $barber->id
                                );

                                $barberRating = $barberReviews->isNotEmpty()
                                    ? round(
                                        (float) $barberReviews->avg('rating'),
                                        1
                                    )
                                    : null;

                                $barberImage = $barber->image_path
                                    ? $resolveMediaUrl($barber->image_path)
                                    : null;
                            @endphp


                            <article class="tcard">

                                <div class="tcard-glow"></div>

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


                                    <div class="t-identity">

                                        <strong class="t-name">
                                            {{ $barber->name }}
                                        </strong>

                                        <span class="t-role">
                                            {{ $barber->specialty ?: 'آرایشگر و متخصص زیبایی' }}
                                        </span>

                                    </div>


                                    <div class="t-rating">

                                        <strong>
                                            @if($barberRating !== null)
                                                {{ number_format($barberRating, 1) }}
                                            @else
                                                —
                                            @endif
                                        </strong>

                                        <span>
                                            {{ number_format($barberReviews->count()) }}
                                            نظر
                                        </span>

                                    </div>

                                </div>


                                @if($barber->bio)

                                    <p class="t-bio">
                                        {{ Str::limit($barber->bio, 145) }}
                                    </p>

                                @endif


                                @if($bookingEnabled)

                                    <div class="t-foot">

                                        <button
                                            type="button"
                                            class="btn-diamond"
                                            data-open-booking
                                            data-barber-id="{{ $barber->id }}"
                                        >
                                            رزرو با {{ $barber->name }}
                                        </button>

                                    </div>

                                @endif

                            </article>

                        @endforeach

                    </div>

                </section>

            @endif


            {{-- =======================================================
                LOCATION
            ======================================================== --}}

            <section
                class="section reveal"
                id="location"
            >

                <div class="section-topline">

                    <div>

                        <span class="section-kicker">
                            06 — LOCATION
                        </span>

                        <h2>
                            موقعیت و اطلاعات سالن
                        </h2>

                        <p>
                            آدرس، ساعات و اطلاعاتی که قبل از مراجعه لازم داری.
                        </p>

                    </div>

                </div>


                <div class="bottom-grid">

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

                                    <strong>
                                        {{ $salon->name }}
                                    </strong>

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
                                <span>⌖</span>
                                <strong>
                                    موقعیت ثبت نشده
                                </strong>
                                <small>
                                    این سالن هنوز موقعیت مکانی دقیقی ثبت نکرده است.
                                </small>
                            </div>

                        @endif

                    </div>


                    <div
                        class="about-card"
                        id="about"
                    >

                        <span class="about-kicker">
                            ABOUT THE SALON
                        </span>

                        <h3>
                            {{ $salon->name }}
                        </h3>


                        <p>
                            {{ $salon->description ?: 'برای این سالن هنوز توضیحی ثبت نشده است.' }}
                        </p>


                        <div class="feat">

                            <div>
                                <i>✦</i>
                                <span>
                                    {{ number_format($postsCount) }}
                                    نمونه‌کار
                                </span>
                            </div>

                            <div>
                                <i>✂</i>
                                <span>
                                    {{ number_format($barbersCount) }}
                                    متخصص
                                </span>
                            </div>

                            <div>
                                <i>◫</i>
                                <span>
                                    {{ number_format($servicesCount) }}
                                    خدمت
                                </span>
                            </div>
</div>


                        <div class="hours">

                            <div>

                                <span class="hours-label">
                                    ساعات امروز
                                </span>

                                <strong>
                                    {{ $todayHoursText ?: 'تعطیل' }}
                                </strong>

                            </div>
</div>

                    </div>

                </div>

            </section>


            {{-- =======================================================
                RELATED
            ======================================================== --}}

            @if($relatedSalons->isNotEmpty())

                <section class="section reveal">

                    <div class="section-topline">

                        <div>

                            <span class="section-kicker">
                                07 — DISCOVER
                            </span>

                            <h2>
                                سالن‌های مشابه
                            </h2>

                            <p>
                                چند گزینه دیگر در همین محدوده.
                            </p>

                        </div>

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

                                    @php
                                        $relatedImage = $related->cover_path
                                            ? $resolveMediaUrl($related->cover_path)
                                            : $relatedLogo;
                                    @endphp

                                    @if($relatedImage)

                                        <img
                                            src="{{ $relatedImage }}"
                                            alt="{{ $related->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <span>
                                            {{ $getInitial($related->name) }}
                                        </span>

                                    @endif

                                </div>


                                <div class="related-body">

                                    <strong class="related-name">
                                        {{ $related->name }}
                                    </strong>


                                    <div class="related-meta">

                                        <span>
                                            @if($related->reviews_avg_rating !== null)
                                                ★
                                                {{ number_format(
                                                    $related->reviews_avg_rating,
                                                    1
                                                ) }}
                                            @else
                                                بدون امتیاز
                                            @endif
                                        </span>


                                        <span>
                                            {{
                                                $related->services_count
                                                ?? 0
                                            }}
                                            خدمت
                                        </span>

                                    </div>

                                </div>

                            </a>

                        @endforeach

                    </div>

                </section>

            @endif


            <div class="page-spacer"></div>

        </main>


        {{-- ==========================================================
            MOBILE BOOKING
        =========================================================== --}}

        @if($bookingEnabled)

            <div class="float-book">

                <button
                    type="button"
                    data-open-booking
                >
                    <span>
                        ◷
                    </span>

                    رزرو نوبت

                    <small>
                        انتخاب زمان
                    </small>
                </button>

            </div>

        @endif


        {{-- ==========================================================
            GALLERY LIGHTBOX
        =========================================================== --}}

        <div
            class="media-lightbox"
            id="mediaLightbox"
            aria-hidden="true"
            hidden
        >

            <div class="lightbox-backdrop"></div>

            <div
                class="lightbox-dialog"
                role="dialog"
                aria-modal="true"
                aria-label="مشاهده رسانه"
            >

                <button
                    type="button"
                    class="lightbox-close"
                    id="lightboxClose"
                    aria-label="بستن"
                >
                    ✕
                </button>


                <button
                    type="button"
                    class="lightbox-nav prev"
                    id="lightboxPrev"
                    aria-label="رسانه قبلی"
                >
                    ‹
                </button>


                <div class="lightbox-media" id="lightboxMedia"></div>


                <button
                    type="button"
                    class="lightbox-nav next"
                    id="lightboxNext"
                    aria-label="رسانه بعدی"
                >
                    ›
                </button>


                <div class="lightbox-info">

                    <span
                        class="lightbox-type"
                        id="lightboxType"
                    >
                    </span>

                    <strong
                        id="lightboxTitle"
                    >
                    </strong>

                    <span
                        id="lightboxMeta"
                    >
                    </span>

                    <p
                        id="lightboxCaption"
                    >
                    </p>

                </div>

            </div>
        </div>


        {{-- ==========================================================
            BOOKING MODAL
        =========================================================== --}}

        @if($bookingEnabled)

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
                                زمانت را انتخاب کن
                            </h2>

                            <p>
                                متخصص و خدمت را انتخاب کن، سپس یکی از
                                زمان‌های آزاد را بردار.
                            </p>

                        </div>


                        <div class="m-filters">

                            <label class="field">

                                <span>
                                    متخصص
                                </span>

                                <select id="filterBarber">

                                    @foreach($barbers as $barber)

                                        <option
                                            value="{{ $barber->id }}"
                                        >
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

                                        <option
                                            value="{{ $service->id }}"
                                        >
                                            {{ $service->name }}

                                            —
                                            {{ $service->duration_minutes }}
                                            دقیقه

                                            @if(
                                                $service->price !== null &&
                                                (float) $service->price > 0
                                            )
                                                —
                                                {{ number_format($service->price) }}
                                                تومان
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </label>

                        </div>


                        <div class="m-grid">

                            <div>

                                <div class="cal-head">

                                    <div>
                                        <small>
                                            تقویم
                                        </small>

                                        <b id="calTitle">
                                            —
                                        </b>
                                    </div>


                                    <div class="cal-nav">

                                        <button
                                            type="button"
                                            data-cal-shift="-1"
                                            aria-label="ماه قبل"
                                        >
                                            ›
                                        </button>

                                        <button
                                            type="button"
                                            data-cal-shift="1"
                                            aria-label="ماه بعد"
                                        >
                                            ‹
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
                                        قابل انتخاب
                                    </i>

                                    <i>
                                        <span class="sq past"></span>
                                        گذشته
                                    </i>

                                </div>

                            </div>


                            <div class="slots-panel">

                                <div class="slots-title">

                                    <div>
                                        <small>
                                            ظرفیت
                                        </small>

                                        <strong>
                                            ساعت‌های خالی
                                        </strong>
                                    </div>

                                    <span id="slotDate">
                                        یک روز انتخاب کن
                                    </span>

                                    <small
                                        id="slotSchedule"
                                        class="slot-schedule"
                                    ></small>

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
                                    <span>متخصص</span>
                                    <b id="sumBarber">—</b>
                                </div>

                                <div class="row">
                                    <span>خدمت</span>
                                    <b id="sumService">—</b>
                                </div>

                                <div class="row">
                                    <span>زمان</span>
                                    <b id="sumTime">—</b>
                                </div>

                            </div>


                            <button
                                type="button"
                                class="btn-confirm"
                                id="confirmBtn"
                                disabled
                            >
                                تأیید و ادامه
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
                            آماده‌ای!
                        </h3>

                        <p id="successText">
                            در حال انتقال به مرحله تأیید رزرو...
                        </p>

                        <button
                            type="button"
                            class="btn-diamond success-close"
                            data-close-booking
                        >
                            بستن
                        </button>

                    </div>

                </div>

            </div>

        @endif


        <footer>
            NOBAT
            <span>✦</span>
            {{ $salon->name }}
            <span>—</span>
            تمام حقوق محفوظ است.
        </footer>


        <div
            class="salon-toast"
            id="salonToast"
            role="status"
            aria-live="polite"
        ></div>

    </div>

@endsection
