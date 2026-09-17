@php
    $heroSlides = collect([$featuredSalon ?? null])
        ->merge($popularSalons ?? collect())
        ->filter()
        ->unique('id')
        ->map(function ($salon) use ($resolveImage) {
            $image =
                ($salon->cover_url ?? null)
                ?: $resolveImage($salon->cover_path ?? null)
                ?: $resolveImage($salon->logo_path ?? null);

            return [
                'id' => $salon->id,
                'name' => $salon->name,
                'image' => $image,
                'url' => route('public.salons.show', $salon),
            ];
        })
        ->filter(fn ($slide) => filled($slide['image']))
        ->take(8)
        ->values();

    $heroResults = ($hasGeo && ($nearbySalons ?? collect())->isNotEmpty())
        ? $nearbySalons->take(3)->values()
        : $salons->take(3)->values();

    $heroResultCount = $isSearchMode
        ? $salons->total()
        : ($hasGeo ? $nearbySalons->count() : $salons->total());

    $heroResultLabel = $hasGeo
        ? 'نتایج نزدیک شما'
        : ($isSearchMode ? 'نتایج جستجو' : 'سالن‌های پیشنهادی');

    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([
            [
                'id' => 'fallback',
                'name' => 'NOBAT',
                'image' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=2200&q=90',
                'url' => route('salons.discover'),
            ],
        ]);
    }
@endphp

<section
    class="discover-hero discover-hero-modern"
    id="hero"
>
    <div class="discover-container discover-hero-modern-grid">
        <div class="discover-hero-modern-content">
            <div>
                <span class="discover-hero-modern-eyebrow">
                    NOBAT · کشف و رزرو
                </span>

                <h1 class="discover-hero-modern-title">
                    سالن مناسب خودت را پیدا کن.
                </h1>

                <p class="discover-hero-modern-description">
                    سالن، متخصص یا خدمت موردنظرت را پیدا کن و مستقیم برای نوبت اقدام کن.
                </p>
            </div>

            <form
                action="{{ route('salons.discover') }}#results"
                method="GET"
                class="discover-hero-search discover-hero-modern-search"
                role="search"
                aria-label="جستجوی سالن و خدمات"
            >
                <label class="discover-hero-search-field discover-hero-search-main">
                    <span class="discover-hero-search-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="6.5" />
                            <path d="m16 16 4.5 4.5" />
                        </svg>
                    </span>

                    <span class="sr-only">جستجوی سالن، خدمت یا متخصص</span>

                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="سالن، خدمت یا متخصص را جستجو کن..."
                        autocomplete="off"
                        enterkeyhint="search"
                    >
                </label>

                <label class="discover-hero-search-field discover-hero-search-location">
                    <span class="discover-hero-search-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 10.5c0 5.2-8 10-8 10s-8-4.8-8-10a8 8 0 1 1 16 0Z" />
                            <circle cx="12" cy="10.5" r="2.5" />
                        </svg>
                    </span>

                    <span class="sr-only">شهر یا منطقه</span>

                    <input
                        type="text"
                        name="city"
                        value="{{ $filters['city'] }}"
                        placeholder="شهر یا منطقه"
                        autocomplete="address-level2"
                    >
                </label>

                <button
                    type="submit"
                    class="discover-hero-search-button"
                >
                    جستجو
                    <span aria-hidden="true">←</span>
                </button>
            </form>

            <div class="discover-hero-features" aria-label="مزیت‌های NOBAT">
                <div class="discover-hero-feature-item">
                    <strong>سالن‌های منتخب</strong>
                    <span>سالن مناسب خودت را سریع پیدا کن</span>
                </div>

                <div class="discover-hero-feature-item">
                    <strong>متخصص‌های حرفه‌ای</strong>
                    <span>آرایشگر مناسب خدمتت را ببین</span>
                </div>

                <div class="discover-hero-feature-item">
                    <strong>خدمات متنوع</strong>
                    <span>خدمت دلخواهت را مقایسه کن</span>
                </div>

                <div class="discover-hero-feature-item">
                    <strong>رزرو سریع</strong>
                    <span>مستقیم وارد فرآیند نوبت شو</span>
                </div>
            </div>

            @if ($heroResults->isNotEmpty())
                <div class="discover-hero-results-preview">
                    <div class="discover-hero-results-heading">
                        <div>
                            <span>{{ $heroResultLabel }}</span>

                            <strong>
                                {{ number_format($heroResultCount) }} سالن
                            </strong>
                        </div>

                        <a href="#results">
                            مشاهده همه
                            <span aria-hidden="true">←</span>
                        </a>
                    </div>

                    <div class="discover-hero-results-grid">
                        @foreach ($heroResults as $heroResult)
                            @php
                                $heroResultImage =
                                    ($heroResult->cover_url ?? null)
                                    ?: $resolveImage($heroResult->cover_path ?? null)
                                    ?: $resolveImage($heroResult->logo_path ?? null);

                                $heroResultLocation = collect([
                                    $heroResult->district ?? null,
                                    $heroResult->city ?? null,
                                ])->filter()->implode('، ');

                                $heroResultRating = (float) ($heroResult->reviews_avg_rating ?? 0);
                            @endphp

                            <article class="discover-hero-result-card">
                                <a
                                    href="{{ route('public.salons.show', $heroResult) }}"
                                    class="discover-hero-result-media"
                                    aria-label="مشاهده {{ $heroResult->name }}"
                                >
                                    @if ($heroResultImage)
                                        <img
                                            src="{{ $heroResultImage }}"
                                            alt="{{ $heroResult->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <span aria-hidden="true">
                                            {{ mb_substr(trim($heroResult->name), 0, 1) }}
                                        </span>
                                    @endif
                                </a>

                                <div class="discover-hero-result-body">
                                    <a
                                        href="{{ route('public.salons.show', $heroResult) }}"
                                        class="discover-hero-result-name"
                                    >
                                        {{ $heroResult->name }}
                                    </a>

                                    <div class="discover-hero-result-meta">
                                        @if ($heroResultRating > 0)
                                            <span>
                                                ★ {{ number_format($heroResultRating, 1) }}
                                            </span>
                                        @endif

                                        @if ($heroResultLocation)
                                            <span>{{ $heroResultLocation }}</span>
                                        @endif

                                        @if ($hasGeo && $heroResult->distance_km !== null)
                                            <span>
                                                {{ number_format((float) $heroResult->distance_km, 1) }} کیلومتر
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div
            class="discover-hero-modern-visual"
            data-discover-hero-slider
            aria-label="کاور سالن‌های منتخب"
        >
            <div class="discover-hero-modern-slider">
                @foreach ($heroSlides as $index => $slide)
                    <a
                        href="{{ $slide['url'] }}"
                        class="discover-hero-modern-slide {{ $index === 0 ? 'is-active' : '' }}"
                        data-hero-slide
                        aria-label="مشاهده {{ $slide['name'] }}"
                    >
                        <img
                            src="{{ $slide['image'] }}"
                            alt="{{ $slide['name'] }}"
                            class="discover-hero-modern-image"
                            {{ $index === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                            decoding="async"
                        >
                    </a>
                @endforeach
            </div>

            <div class="discover-hero-modern-counter" aria-hidden="true">
                <span>01</span>
                <i></i>
                <span>{{ str_pad((string) $heroSlides->count(), 2, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>
</section>
