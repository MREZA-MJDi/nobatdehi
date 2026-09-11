<section class="discover-hero">
    <div class="discover-container discover-hero-inner">

        {{-- Media --}}
        <div class="discover-hero-media reveal-item">

            @php
                $heroSalon = $featuredSalon ?? $salons->first();
                $heroImage = $heroSalon?->cover_url;
            @endphp

            @if($heroImage)
                <img
                    src="{{ $heroImage }}"
                    alt="{{ $heroSalon?->name ?? 'سالن‌های NOBAT' }}"
                    fetchpriority="high"
                    decoding="async"
                >
            @else
                <div
                    class="discover-hero-placeholder"
                    aria-hidden="true"
                >
                    <span>NOBAT</span>
                </div>
            @endif

            <div class="discover-media-label">
                <span class="discover-media-label-dot"></span>
                سالن‌ها و متخصص‌های فعال
            </div>

        </div>


        {{-- Content --}}
        <div class="discover-hero-content reveal-item">

            <span class="discover-eyebrow">
                کشف کن، مقایسه کن، انتخاب کن
            </span>

            <h1>
                سالن مناسب تو،
                <span>همین‌جاست.</span>
            </h1>

            <p class="discover-hero-description">
                از بین سالن‌های زنانه و مردانه، لیزر، ناخن، پوست
                و متخصص‌های شهر بگرد، مقایسه کن و انتخابت را راحت‌تر انجام بده.
            </p>


            {{-- Search --}}
            <form
                action="{{ route('salons.discover') }}"
                method="GET"
                class="discover-search"
                role="search"
            >
                <label
                    for="discover-search-input"
                    class="sr-only"
                >
                    جستجوی سالن، خدمت یا متخصص
                </label>

                <div class="discover-search-icon" aria-hidden="true">
                    ⌕
                </div>

                <input
                    id="discover-search-input"
                    type="search"
                    name="q"
                    value="{{ $search ?? request('q') }}"
                    placeholder="مثلاً لیزر، فید، رنگ مو یا نام سالن..."
                    autocomplete="off"
                    enterkeyhint="search"
                >

                <button
                    type="submit"
                    class="discover-search-button"
                >
                    جستجو
                </button>
            </form>


            {{-- Quick searches --}}
            <div
                class="discover-quick-search"
                aria-label="جستجوهای سریع"
            >
                <span>محبوب:</span>

                <a href="{{ route('salons.discover', ['q' => 'کوتاهی']) }}">
                    کوتاهی
                </a>

                <a href="{{ route('salons.discover', ['q' => 'فید']) }}">
                    فید
                </a>

                <a href="{{ route('salons.discover', ['q' => 'رنگ مو']) }}">
                    رنگ مو
                </a>

                <a href="{{ route('salons.discover', ['q' => 'لیزر']) }}">
                    لیزر
                </a>

                <a href="{{ route('salons.discover', ['q' => 'ناخن']) }}">
                    ناخن
                </a>
            </div>


            {{-- Trust / Stats --}}
            <div class="discover-trust">

                <div class="discover-trust-item">
                    <strong>
                        {{ number_format($stats['salons'] ?? 0) }}
                    </strong>

                    <span>
                        سالن فعال
                    </span>
                </div>

                <div class="discover-trust-item">
                    <strong>
                        {{ number_format($stats['barbers'] ?? 0) }}
                    </strong>

                    <span>
                        متخصص
                    </span>
                </div>

                <div class="discover-trust-item">
                    <strong>
                        {{ number_format($stats['services'] ?? 0) }}
                    </strong>

                    <span>
                        خدمت
                    </span>
                </div>

            </div>

        </div>

    </div>
</section>
