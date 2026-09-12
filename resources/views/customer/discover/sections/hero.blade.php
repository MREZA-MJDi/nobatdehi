<section
    class="discovery-hero"
    id="hero"
>

    <div class="discovery-container discovery-hero-inner">

        {{-- CONTENT --}}
        <div class="discovery-hero-content">

            <span class="discovery-eyebrow">
                کشف کن، انتخاب کن، نوبت بگیر
            </span>

            <h1 class="discovery-hero-title">

                سالن مناسب
                <br>

                <span>
                    خودت را پیدا کن.
                </span>

            </h1>

            <p class="discovery-hero-description">

                سالن‌ها، متخصص‌ها و خدمات موردنظرت را پیدا کن،
                مقایسه کن و بدون تماس اضافه نوبت بگیر.

            </p>


            {{-- SEARCH --}}
            <form
                action="{{ route('salons.discover') }}"
                method="GET"
                class="discovery-search-box"
                role="search"
            >

                <label class="discovery-search-field">

                    <svg
                        class="discovery-search-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >

                        <circle
                            cx="11"
                            cy="11"
                            r="6.5"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                        <path
                            d="m16 16 5 5"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />

                    </svg>

                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="سالن، خدمت یا متخصص را جستجو کن..."
                        autocomplete="off"
                    >

                </label>


                <label class="discovery-search-field">

                    <svg
                        class="discovery-search-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >

                        <path
                            d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                        <circle
                            cx="12"
                            cy="10"
                            r="2.5"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />

                    </svg>

                    <input
                        type="text"
                        name="city"
                        value="{{ request('city') }}"
                        placeholder="شهر یا منطقه"
                    >

                </label>


                <button
                    type="submit"
                    class="discovery-search-submit"
                >
                    جستجو
                </button>

            </form>


            {{-- QUICK SEARCH --}}
            <div class="discovery-quick-search">

                <span class="discovery-quick-label">
                    محبوب:
                </span>

                @foreach([
                    'مو',
                    'ناخن',
                    'پوست',
                    'میکاپ',
                    'اصلاح',
                    'ماساژ'
                ] as $item)

                    <a
                        href="{{ route(
                            'salons.discover',
                            ['q' => $item]
                        ) }}"
                        class="discovery-quick-link"
                    >
                        {{ $item }}
                    </a>

                @endforeach

            </div>

        </div>


        {{-- IMAGE --}}
        <div class="discovery-hero-media">

            <img
                src="{{ $heroImage }}"
                alt="{{ $heroSalon?->name ?? 'NOBAT' }}"
                fetchpriority="high"
                decoding="async"
            >

            <div class="discovery-hero-media-badge">

                <span class="discovery-hero-media-badge-dot"></span>

                سالن‌های منتخب NOBAT

            </div>

        </div>

    </div>

</section>
