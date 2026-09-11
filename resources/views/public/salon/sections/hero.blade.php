<section
    class="salon-hero"
    style="
        --salon-primary: {{ $salon->primary_color ?: '#6757E8' }};
        --salon-secondary: {{ $salon->secondary_color ?: '#37B8C8' }};
        "
>
    <div class="salon-container">

        <div class="salon-hero-shell">

            {{-- =====================================================
                BACKGROUND IMAGE
            ====================================================== --}}

            <div class="salon-hero-background">

                @if($salon->cover_path)

                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($salon->cover_path) }}"
                        alt="{{ $salon->name }}"
                        class="salon-hero-cover"
                        fetchpriority="high"
                        decoding="async"
                    >

                @else

                    <div class="salon-hero-fallback"></div>

                @endif

            </div>


            {{-- =====================================================
                COLOR / DEPTH LAYERS
            ====================================================== --}}

            <div class="salon-hero-overlay"></div>

            <div class="salon-hero-glow salon-hero-glow-primary"></div>
            <div class="salon-hero-glow salon-hero-glow-secondary"></div>


            {{-- =====================================================
                TOP NAV
            ====================================================== --}}

            <div class="salon-hero-topbar">

                <a
                    href="{{ route('salons.discover') }}"
                    class="salon-hero-back"
                >
                    <span aria-hidden="true">→</span>
                    <span>بازگشت به سالن‌ها</span>
                </a>


                <div class="salon-hero-top-actions">

                    <a
                        href="#portfolio"
                        class="salon-hero-top-link"
                    >
                        نمونه‌کار
                    </a>

                    <a
                        href="#services"
                        class="salon-hero-top-link"
                    >
                        خدمات
                    </a>

                    <a
                        href="#team"
                        class="salon-hero-top-link"
                    >
                        تیم
                    </a>

                </div>

            </div>


            {{-- =====================================================
                MAIN HERO CONTENT
            ====================================================== --}}

            <div class="salon-hero-content">

                {{-- Brand --}}
                <div class="salon-hero-brand">

                    <div class="salon-hero-logo">

                        @if($salon->logo_path)

                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($salon->logo_path) }}"
                                alt="{{ $salon->name }}"
                                loading="eager"
                                decoding="async"
                            >

                        @else

                            <span>
                                {{ $salonInitial }}
                            </span>

                        @endif

                    </div>


                    <div class="salon-hero-brand-copy">

                        <span class="salon-hero-kicker">
                            NOBAT / SALON
                        </span>

                        <span class="salon-hero-brand-name">
                            {{ $salon->name }}
                        </span>

                    </div>

                </div>


                {{-- Status --}}
                <div class="salon-hero-status-row">

                    @if($salon->is_active)

                        <span class="salon-hero-status is-active">
                            <span></span>
                            آماده رزرو
                        </span>

                    @else

                        <span class="salon-hero-status is-closed">
                            <span></span>
                            موقتاً بسته
                        </span>

                    @endif


                    @if($salon->city || $salon->district)

                        <span class="salon-hero-location">

                            <span aria-hidden="true">
                                ⌖
                            </span>

                            {{ collect([
                                $salon->district,
                                $salon->city,
                            ])->filter()->implode('، ') }}

                        </span>

                    @endif

                </div>


                {{-- Name --}}
                <div class="salon-hero-title-wrap">

                    <h1 class="salon-hero-title">
                        {{ $salon->name }}
                    </h1>

                    @if($salon->description)

                        <p class="salon-hero-description">
                            {{ \Illuminate\Support\Str::limit(
                                strip_tags($salon->description),
                                220
                            ) }}
                        </p>

                    @endif

                </div>


                {{-- Meta --}}
                <div class="salon-hero-meta">

                    <div class="salon-hero-meta-item">

                        <span class="salon-hero-meta-icon" aria-hidden="true">
                            ★
                        </span>

                        <div>

                            <strong>
                                {{ $salon->reviews_avg_rating
                                    ? number_format(
                                        (float) $salon->reviews_avg_rating,
                                        1
                                    )
                                    : 'جدید'
                                }}
                            </strong>

                            <small>
                                امتیاز
                            </small>

                        </div>

                    </div>


                    <div class="salon-hero-meta-divider"></div>


                    <div class="salon-hero-meta-item">

                        <div>
                            <strong>
                                {{ number_format($services->count()) }}
                            </strong>

                            <small>
                                خدمت
                            </small>
                        </div>

                    </div>


                    <div class="salon-hero-meta-divider"></div>


                    <div class="salon-hero-meta-item">

                        <div>
                            <strong>
                                {{ number_format($barbers->count()) }}
                            </strong>

                            <small>
                                متخصص
                            </small>
                        </div>

                    </div>

                </div>


                {{-- Quick actions --}}
                <div class="salon-hero-actions">

                    @if($salon->is_active)

                        <a
                            href="#booking"
                            class="salon-hero-booking-button"
                        >
                            <span>
                                رزرو نوبت
                            </span>

                            <span aria-hidden="true">
                                ←
                            </span>
                        </a>

                    @endif


                    <a
                        href="#portfolio"
                        class="salon-hero-secondary-button"
                    >
                        دیدن نمونه‌کارها

                        <span aria-hidden="true">
                            ↓
                        </span>
                    </a>

                </div>

            </div>


            {{-- =====================================================
                FLOATING BRAND CARD
            ====================================================== --}}

            <div class="salon-hero-floating-card">

                <span class="salon-hero-floating-label">
                    {{ $salon->code ?: 'NOBAT' }}
                </span>

                <strong>
                    {{ $salon->name }}
                </strong>

                @if($fullAddress)

                    <span>
                        {{ \Illuminate\Support\Str::limit(
                            $fullAddress,
                            55
                        ) }}
                    </span>

                @endif

            </div>


            {{-- =====================================================
                SCROLL INDICATOR
            ====================================================== --}}

            <a
                href="#portfolio"
                class="salon-hero-scroll"
                aria-label="رفتن به نمونه‌کارها"
            >
                <span>
                    SCROLL
                </span>

                <span aria-hidden="true">
                    ↓
                </span>
            </a>

        </div>

    </div>
</section>
