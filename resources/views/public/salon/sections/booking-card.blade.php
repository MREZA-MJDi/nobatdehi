@php
    $activeServices = $services
        ->where('is_active', true)
        ->sortBy([
            ['sort_order', 'asc'],
            ['name', 'asc'],
        ]);

    $activeBarbers = $barbers
        ->where('is_active', true)
        ->sortBy('name');

    $rating = $salon->reviews_avg_rating ?? null;
    $reviewsCount = $reviews->count();
@endphp

<aside
    id="booking"
    class="salon-booking-card"
    x-data
>
    <div class="salon-booking-card-inner">

        {{-- =====================================================
            TOP
        ====================================================== --}}

        <div class="salon-booking-heading">

            <div class="salon-booking-heading-icon" aria-hidden="true">
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M8 2v4"/>
                    <path d="M16 2v4"/>
                    <path d="M3 9h18"/>
                    <path d="M8 13h3"/>
                    <path d="M8 17h5"/>
                </svg>
            </div>

            <div>
                <span class="salon-booking-eyebrow">
                    رزرو آنلاین
                </span>

                <h2>
                    نوبتت را همین‌جا بگیر
                </h2>
            </div>

        </div>


        {{-- =====================================================
            TRUST
        ====================================================== --}}

        <div class="salon-booking-trust">

            @if($rating)

                <div class="salon-booking-trust-item">

                    <span
                        class="salon-booking-trust-value"
                        dir="ltr"
                    >
                        {{ number_format((float) $rating, 1) }}
                        <span aria-hidden="true">★</span>
                    </span>

                    <span class="salon-booking-trust-label">
                        امتیاز
                    </span>

                </div>

            @endif


            <div class="salon-booking-trust-item">

                <span class="salon-booking-trust-value">
                    {{ number_format($activeServices->count()) }}
                </span>

                <span class="salon-booking-trust-label">
                    خدمت
                </span>

            </div>


            <div class="salon-booking-trust-item">

                <span class="salon-booking-trust-value">
                    {{ number_format($activeBarbers->count()) }}
                </span>

                <span class="salon-booking-trust-label">
                    متخصص
                </span>

            </div>

        </div>


        {{-- =====================================================
            EXPLAIN
        ====================================================== --}}

        <div class="salon-booking-intro">

            <p>
                خدمت و متخصصت را انتخاب کن؛
                بعد تاریخ و ساعت‌های آزاد را ببین.
            </p>

        </div>


        {{-- =====================================================
            PRIMARY ACTION
        ====================================================== --}}

        @if($salon->is_active && $activeServices->isNotEmpty())

            <button
                type="button"
                @click="$dispatch('open-booking')"
                class="salon-booking-primary"
            >
                <span class="salon-booking-primary-main">

                    <span class="salon-booking-primary-icon" aria-hidden="true">
                        <svg
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 3v4"/>
                            <path d="M18 3v4"/>
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M3 10h18"/>
                            <path d="M8 14h3"/>
                            <path d="M8 18h5"/>
                        </svg>
                    </span>

                    <span>
                        <strong>
                            رزرو نوبت
                        </strong>

                        <small>
                            مشاهده زمان‌های آزاد
                        </small>
                    </span>

                </span>

                <span
                    class="salon-booking-primary-arrow"
                    aria-hidden="true"
                >
                    ←
                </span>
            </button>

        @elseif(!$salon->is_active)

            <div class="salon-booking-disabled">

                <span class="salon-booking-disabled-icon" aria-hidden="true">
                    ×
                </span>

                <div>
                    <strong>
                        رزرو موقتاً غیرفعال است
                    </strong>

                    <span>
                        این سالن در حال حاضر نوبت آنلاین نمی‌پذیرد.
                    </span>
                </div>

            </div>

        @else

            <div class="salon-booking-disabled">

                <span class="salon-booking-disabled-icon" aria-hidden="true">
                    !
                </span>

                <div>
                    <strong>
                        هنوز خدمتی برای رزرو ثبت نشده
                    </strong>

                    <span>
                        این سالن هنوز خدماتی برای رزرو آنلاین ثبت نکرده است.
                    </span>
                </div>

            </div>

        @endif


        {{-- =====================================================
            MINI SERVICE PREVIEW
        ====================================================== --}}

        @if($activeServices->isNotEmpty())

            <div class="salon-booking-services">

                <div class="salon-booking-services-head">

                    <span>
                        خدمات محبوب
                    </span>

                    <span>
                        {{ number_format($activeServices->count()) }}
                    </span>

                </div>


                <div class="salon-booking-service-list">

                    @foreach($activeServices->take(4) as $service)

                        <div class="salon-booking-service">

                            <div class="salon-booking-service-info">

                                <strong>
                                    {{ $service->name }}
                                </strong>

                                @if($service->duration_minutes)

                                    <span>
                                        {{ number_format($service->duration_minutes) }}
                                        دقیقه
                                    </span>

                                @endif

                            </div>


                            @if($service->price !== null)

                                <strong
                                    class="salon-booking-service-price"
                                    dir="ltr"
                                >
                                    {{ number_format((float) $service->price) }}
                                    <small>
                                        تومان
                                    </small>
                                </strong>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        {{-- =====================================================
            BOOKING PROMISE
        ====================================================== --}}

        <div class="salon-booking-note">

            <div
                class="salon-booking-note-icon"
                aria-hidden="true"
            >
                ✓
            </div>

            <p>
                زمان انتخاب‌شده هنگام ثبت نهایی دوباره بررسی می‌شود
                تا نوبت تکراری ثبت نشود.
            </p>

        </div>


        {{-- =====================================================
            LOGIN NOTE
        ====================================================== --}}

        <div class="salon-booking-login-hint">

            <span aria-hidden="true">
                ◌
            </span>

            <span>
                برای ثبت نهایی نوبت، در صورت نیاز وارد حساب مشتری می‌شوی.
            </span>

        </div>

    </div>
</aside>
