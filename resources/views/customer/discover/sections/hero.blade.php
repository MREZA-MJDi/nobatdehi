@php
    $heroLocation = collect([
        $heroSalon?->district,
        $heroSalon?->city,
    ])->filter()->implode('، ');

    $heroSlides = collect([$featuredSalon ?? null])
        ->merge($popularSalons ?? collect())
        ->filter()
        ->unique('id')
        ->take(5)
        ->values()
        ->map(function ($salon) use ($resolveImage, $heroImage) {
            $image =
                ($salon->cover_url ?? null)
                ?: $resolveImage($salon->cover_path ?? null)
                ?: $resolveImage($salon->logo_path ?? null)
                ?: $heroImage;

            return [
                'id' => $salon->id,
                'name' => $salon->name,
                'location' => collect([
                    $salon->district,
                    $salon->city,
                ])->filter()->implode('، ') ?: 'سالن منتخب امروز',
                'image' => $image,
                'url' => route('public.salons.show', $salon),
            ];
        });
@endphp

<section
    class="discover-hero"
    id="hero"
    data-discover-hero-slider
    aria-label="سالن‌های منتخب NOBAT"
>
    <div class="discover-hero-slides" aria-hidden="true">
        @forelse($heroSlides as $index => $slide)
            <div
                class="discover-hero-slide{{ $index === 0 ? ' is-active' : '' }}"
                data-discover-hero-slide
                data-hero-name="{{ $slide['name'] }}"
                data-hero-location="{{ $slide['location'] }}"
                data-hero-url="{{ $slide['url'] }}"
            >
                <img
                    src="{{ $slide['image'] }}"
                    alt=""
                    @if($index === 0) fetchpriority="high" @endif
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    decoding="async"
                >
            </div>
        @empty
            <div class="discover-hero-slide is-active">
                <img
                    src="{{ $heroImage }}"
                    alt=""
                    fetchpriority="high"
                    decoding="async"
                >
            </div>
        @endforelse
    </div>

    <div class="discover-hero-backdrop" aria-hidden="true"></div>
    <div class="discover-hero-grain" aria-hidden="true"></div>

    <div class="discover-container discover-hero-inner">
        <div class="discover-hero-content">
            <p class="discover-hero-eyebrow">
                زیبایی، استایل و مراقبت · برای هر سلیقه
            </p>

            <h1 class="discover-hero-title" style="font-size: clamp(2.85rem, 6.6vw, 5.15rem);">
                جای بعدیِ تو، همین‌جاست.
            </h1>

            <p class="discover-hero-description">
                سالن، متخصص یا خدمات موردنظرت را پیدا کن و مستقیم برای نوبت اقدام کن.
                برای هر استایل، هر سلیقه و هر آدمی.
            </p>

            <form
                action="{{ route('salons.discover') }}#results"
                method="GET"
                class="discover-hero-search"
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
                        aria-label="سالن، خدمت یا متخصص"
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
                        aria-label="شهر یا منطقه"
                        autocomplete="address-level2"
                    >
                </label>

                <button
                    type="submit"
                    class="discover-hero-search-button"
                >
                    <span>جستجو</span>
                    <span aria-hidden="true">←</span>
                </button>
            </form>

            <div class="discover-hero-popular" aria-label="جستجوهای محبوب">
                <span>محبوب اینجا:</span>
                @foreach(['مو', 'ناخن', 'پوست', 'میکاپ', 'اصلاح', 'ماساژ'] as $item)
                    <a href="{{ route('salons.discover', ['q' => $item]) }}#results">
                        {{ $item }}
                    </a>
                @endforeach
            </div>
        </div>

        @php
            $initialHero = $heroSlides->first();
            $initialHeroName = $initialHero['name'] ?? ($heroSalon?->name ?? 'NOBAT');
            $initialHeroLocation = $initialHero['location'] ?? ($heroLocation ?: 'سالن منتخب امروز');
            $initialHeroUrl = $initialHero['url'] ?? ($heroSalon ? route('public.salons.show', $heroSalon) : route('salons.discover'));
        @endphp

        <a
            href="{{ $initialHeroUrl }}"
            class="discover-hero-feature"
            data-discover-hero-feature
            aria-label="مشاهده {{ $initialHeroName }}"
        >
            <span class="discover-hero-feature-meta">
                <span>انتخاب NOBAT</span>
                <span aria-hidden="true">↙</span>
            </span>
            <strong data-discover-hero-name>{{ $initialHeroName }}</strong>
            <span data-discover-hero-location>{{ $initialHeroLocation }}</span>
        </a>

        <a href="#results" class="discover-hero-scroll" aria-label="مشاهده نتایج">
            <span>کشف کن</span>
            <span class="discover-hero-scroll-line" aria-hidden="true"></span>
            <span aria-hidden="true">↓</span>
        </a>
    </div>
</section>
