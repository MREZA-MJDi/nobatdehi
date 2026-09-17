<section
    class="discover-hero"
    id="hero"
>

    <div class="discover-container discover-hero-grid">

        <div class="discover-hero-content">

            <span class="discover-kicker">
                پیدا کن · مقایسه کن · نوبت بگیر
            </span>

            <h1 class="discover-hero-title">

                سالن مناسب
                <span>
                    خودت را پیدا کن.
                </span>

            </h1>

            <p class="discover-hero-description">
                سالن‌ها، متخصص‌ها و خدمات موردنظرت را پیدا کن،
                مقایسه کن و در چند قدم نوبتت را ثبت کن.
            </p>


            <form
                action="{{ route('salons.discover') }}#results"
                method="GET"
                class="discover-hero-search"
                role="search"
            >

                <div class="discover-hero-search-field">

                    <span aria-hidden="true">
                        ⌕
                    </span>

                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="سالن، خدمت یا متخصص را جستجو کن..."
                        autocomplete="off"
                    >

                </div>


                <div class="discover-hero-search-field">

                    <span aria-hidden="true">
                        ◎
                    </span>

                    <input
                        type="text"
                        name="city"
                        value="{{ $filters['city'] }}"
                        placeholder="شهر یا منطقه"
                    >

                </div>


                <button
                    type="submit"
                    class="discover-hero-search-button"
                >
                    جستجو
                </button>

            </form>


            <div class="discover-hero-popular">

                <span>
                    محبوب:
                </span>

                @foreach([
                    'مو',
                    'ناخن',
                    'پوست',
                    'میکاپ',
                    'اصلاح',
                    'ماساژ',
                ] as $item)

                    <a
                        href="{{ route(
                            'salons.discover',
                            ['q' => $item]
                        ) }}#results"
                    >
                        {{ $item }}
                    </a>

                @endforeach

            </div>


            <a
                href="#results"
                class="discover-hero-results-link"
            >
                مشاهده نتایج و فیلترها
                <span aria-hidden="true">
                    ↓
                </span>
            </a>

        </div>


        <div class="discover-hero-media">

            <img
                src="{{ $heroImage }}"
                alt="{{ $heroSalon?->name ?? 'NOBAT' }}"
                fetchpriority="high"
                decoding="async"
            >

            <div class="discover-hero-overlay"></div>


            @if($heroSalon)

                <div class="discover-hero-card">

                    <span class="discover-hero-card-label">
                        سالن منتخب
                    </span>

                    <strong>
                        {{ $heroSalon->name }}
                    </strong>

                    <span>
                        {{ collect([
                            $heroSalon->district,
                            $heroSalon->city,
                        ])->filter()->implode('، ')
                        ?: 'NOBAT' }}
                    </span>

                </div>

            @endif

        </div>

    </div>

</section>
