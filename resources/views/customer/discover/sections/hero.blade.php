@php
    $heroLocation = collect([
        $heroSalon?->district,
        $heroSalon?->city,
    ])->filter()->implode('، ');
@endphp

<section
    class="discover-hero"
    id="hero"
    style="--discover-hero-image: url('{{ $heroImage }}');"
>
    <div class="discover-hero-backdrop" aria-hidden="true"></div>
    <div class="discover-hero-grain" aria-hidden="true"></div>

    <div class="discover-container discover-hero-inner">
        <div class="discover-hero-content">
            <p class="discover-hero-eyebrow">
                زیبایی، استایل و مراقبت · برای هر سلیقه
            </p>

            <h1 class="discover-hero-title">
                جای بعدیِ تو، همین‌جاست.
            </h1>

            <p class="discover-hero-description">
                سالن، متخصص یا خدمات موردنظرت را پیدا کن و مستقیم برای نوبت اقدام کن.
            </p>

            <form
                action="{{ route('salons.discover') }}#results"
                method="GET"
                class="discover-hero-search"
                role="search"
            >
                <label class="discover-hero-search-field discover-hero-search-main">
                    <span class="discover-hero-search-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="6.5" />
                            <path d="m16 16 4.5 4.5" />
                        </svg>
                    </span>
                    <span class="sr-only">جستجو</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="سالن، خدمت یا متخصص..."
                        autocomplete="off"
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

            <div class="discover-hero-popular" aria-label="جستجوهای محبوب">
                <span>محبوب اینجا:</span>
                @foreach([
                    'مو',
                    'ناخن',
                    'پوست',
                    'میکاپ',
                    'اصلاح',
                    'ماساژ',
                ] as $item)
                    <a href="{{ route('salons.discover', ['q' => $item]) }}#results">
                        {{ $item }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($heroSalon)
            <a
                href="{{ route('salons.show', $heroSalon) }}"
                class="discover-hero-feature"
            >
                <span class="discover-hero-feature-meta">
                    انتخاب NOBAT
                    <span aria-hidden="true">↙</span>
                </span>
                <strong>{{ $heroSalon->name }}</strong>
                <span>{{ $heroLocation ?: 'سالن منتخب امروز' }}</span>
            </a>
        @endif

        <a href="#results" class="discover-hero-scroll" aria-label="مشاهده نتایج">
            <span>کشف کن</span>
            <span class="discover-hero-scroll-line" aria-hidden="true"></span>
        </a>
    </div>
</section>
