<section class="discovery-hero discovery-hero-immersive" id="hero">
    <div class="discovery-container discovery-hero-inner">
        <div class="discovery-hero-content">
            <span class="discovery-eyebrow">
                پیدا کن · مقایسه کن · نوبت بگیر
            </span>

            <h1 class="discovery-hero-title">
                سالن مناسب
                <span>خودت را پیدا کن.</span>
            </h1>

            <p class="discovery-hero-description">
                سالن، متخصص یا خدمت موردنظرت را جست‌وجو کن، نتیجه‌ها را مقایسه کن
                و مستقیماً برای زمان مناسب نوبت بگیر.
            </p>

            <form
                action="{{ route('salons.discover') }}#results"
                method="GET"
                class="discovery-search-box"
                role="search"
            >
                <label class="discovery-search-field">
                    <span class="discovery-search-icon" aria-hidden="true">⌕</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="سالن، خدمت یا متخصص را جست‌وجو کن..."
                        autocomplete="off"
                        aria-label="جستجوی سالن، خدمت یا متخصص"
                    >
                </label>

                <label class="discovery-search-field">
                    <span class="discovery-search-icon" aria-hidden="true">◎</span>
                    <input
                        type="text"
                        name="city"
                        value="{{ $filters['city'] }}"
                        placeholder="شهر یا منطقه"
                        autocomplete="address-level2"
                        aria-label="شهر یا منطقه"
                    >
                </label>

                <button
                    type="button"
                    class="discovery-search-nearby"
                    data-discover-nearby
                >
                    <span aria-hidden="true">⌖</span>
                    نزدیک من
                </button>

                <button type="submit" class="discovery-search-submit">
                    جستجو
                </button>
            </form>

            <div class="discovery-quick-search" aria-label="جستجوی سریع">
                <span class="discovery-quick-label">محبوب:</span>

                @foreach(['مو', 'ناخن', 'پوست', 'میکاپ', 'اصلاح', 'ماساژ'] as $item)
                    <a
                        href="{{ route('salons.discover', ['q' => $item]) }}#results"
                        class="discovery-quick-link"
                    >
                        {{ $item }}
                    </a>
                @endforeach
            </div>
        </div>

        @php
            $heroSlides = collect([$featuredSalon ?? null])
                ->merge($popularSalons ?? collect())
                ->filter()
                ->unique('id')
                ->take(6)
                ->values();
        @endphp

        <div
            class="discovery-hero-media"
            data-discover-hero-slider
            aria-label="سالن‌های منتخب"
        >
            @forelse($heroSlides as $index => $slide)
                @php
                    $image =
                        $resolveImage($slide->cover_path ?? null)
                        ?: $resolveImage($slide->logo_path ?? null)
                        ?: 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1800&q=88';

                    $location = collect([
                        $slide->district,
                        $slide->city,
                    ])->filter()->implode('، ') ?: 'NOBAT';
                @endphp

                <img
                    src="{{ $image }}"
                    alt="{{ $slide->name }}"
                    data-hero-name="{{ $slide->name }}"
                    data-hero-location="{{ $location }}"
                    data-hero-url="{{ route('public.salons.show', $slide) }}"
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    decoding="async"
                    fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                    style="position:absolute;inset:0;opacity:{{ $index === 0 ? '1' : '0' }};transition:opacity .55s ease;"
                >
            @empty
                <img
                    src="https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1800&q=88"
                    alt="NOBAT"
                    data-hero-name="NOBAT"
                    data-hero-location=""
                    data-hero-url="{{ route('salons.discover') }}"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                    style="position:absolute;inset:0;opacity:1;transition:opacity .55s ease;"
                >
            @endforelse

            <a
                href="{{ $heroSlides->first() ? route('public.salons.show', $heroSlides->first()) : route('salons.discover') }}"
                class="discovery-hero-media-badge"
                data-hero-badge
            >
                <span class="discovery-hero-media-badge-dot"></span>
                <span data-hero-badge-text>
                    {{ $heroSlides->first()?->name ?? 'NOBAT' }}
                </span>
            </a>
        </div>
    </div>
</section>
