@php
    use App\Http\Controllers\PublicSite\HomeController;

    $hero = $featuredSalons->first();
    $heroLocation = $hero
        ? trim(collect([$hero->city, $hero->district])->filter()->implode('، '))
        : null;
@endphp

<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#080808">
    <meta name="description" content="NOBAT؛ پیدا کن، انتخاب کن، نوبت بگیر.">
    <title>NOBAT — پیدا کن، انتخاب کن، نوبت بگیر</title>
    @vite(['resources/css/home.css', 'resources/js/home.js'])
</head>
<body>

<main class="nobat-shell">
    <svg class="stage-deco" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <path d="M 400 -50 Q 700 120 1080 100 Q 1350 85 1520 220"/>
        <path d="M 1560 280 Q 1470 480 1490 680 Q 1510 850 1420 1000"/>
        <path d="M -50 860 Q 300 940 700 890 Q 1100 840 1560 950"/>
        <circle cx="180" cy="150" r="3"/>
    </svg>

    <div class="frame">
        <span class="frame-rail rail-top"></span>
        <span class="frame-rail rail-right"></span>
        <span class="frame-rail rail-bottom"></span>
        <span class="frame-rail rail-left"></span>

        <header class="nav">
            <a class="nav-left" href="{{ route('home') }}" aria-label="NOBAT">
                <span class="logo">NOBAT</span>
                <span class="logo-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2 L14 10 L22 12 L14 14 L12 22 L10 14 L2 12 L10 10 Z"/>
                    </svg>
                </span>
            </a>

            <nav class="nav-menu" aria-label="منوی اصلی">
                <a class="active" href="#discover">کشف</a>
                <a href="#services">خدمات</a>
                <a href="#lookbook">نمونه‌کارها</a>
                <a href="{{ route('salons.discover') }}">همه سالن‌ها</a>
            </nav>

            <div class="nav-right">
                <a class="nav-icon" href="{{ route('salons.discover') }}" aria-label="جستجو">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="16" y1="16" x2="21" y2="21"/>
                    </svg>
                </a>
                <a class="nav-enter" href="{{ route('login') }}">ورود</a>
            </div>
        </header>

        <section class="hero" id="discover" data-home-slider>
            <div class="bg-word" aria-hidden="true">NOBAT</div>

            <div class="hero-copy">
                <span class="eyebrow"><i></i> تجربه زیبایی، انتخاب‌شده برای تو</span>

                <h1 class="main-word">
                    <span class="word-red">پیدا</span>
                    <span class="word-light">کن.</span>
                </h1>

                <p class="subtitle">
                    سالن، خدمات و استایلیستی که واقعاً به سلیقه‌ات می‌خوره.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('salons.discover') }}" class="cta cta-primary">
                        شروع کشف <span>←</span>
                    </a>
                    <a href="#services" class="cta cta-ghost">دیدن خدمات</a>
                </div>

                <div class="hero-stats">
                    <div><strong>{{ number_format($stats['salons']) }}+</strong><span>سالن فعال</span></div>
                    <div><strong>{{ number_format($stats['services']) }}+</strong><span>خدمت</span></div>
                    <div><strong>{{ number_format($stats['barbers']) }}+</strong><span>استایلیست</span></div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="visual-index">
                    <span id="slideCurrent">01</span><i></i>
                    <span>{{ str_pad((string) max(1, $featuredSalons->count()), 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="product-stage" id="heroSlides">
                    @forelse($featuredSalons as $index => $salon)
                        @php
                            $cover = HomeController::mediaUrl($salon->cover_path);
                            $post = $salon->posts()->where('is_active', true)->latest('id')->first();
                            $postImage = $post ? HomeController::mediaUrl($post->thumbnail_path ?: $post->media_path) : null;
                            $image = $postImage ?: $cover;
                            $location = trim(collect([$salon->city, $salon->district])->filter()->implode('، '));
                        @endphp

                        <article
                            class="slide {{ $index === 0 ? 'is-active' : '' }}"
                            data-name="{{ $salon->name }}"
                            data-location="{{ $location ?: 'NOBAT' }}"
                            data-rating="{{ $salon->reviews_avg_rating ? number_format((float) $salon->reviews_avg_rating, 1) : '—' }}"
                            data-services="{{ (int) ($salon->services_count ?? 0) }}"
                            data-url="{{ route('public.salons.show', ['salon' => $salon->slug]) }}"
                        >
                            <div class="slide-image-wrap">
                                @if($image)
                                    <img src="{{ $image }}" alt="{{ $salon->name }}" class="slide-image">
                                @else
                                    <div class="slide-placeholder">
                                        <span>NOBAT</span>
                                        <small>NO IMAGE</small>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <article class="slide is-active">
                            <div class="slide-image-wrap">
                                <div class="slide-placeholder">
                                    <span>NOBAT</span>
                                    <small>هنوز سالنی ثبت نشده</small>
                                </div>
                            </div>
                        </article>
                    @endforelse
                </div>

                <div class="hero-card">
                    <div class="hero-card-main">
                        <span class="badge-exclusive">منتخب NOBAT</span>
                        <span class="meta-brand" id="slideLocation">{{ $heroLocation ?: 'کشف سالن‌ها' }}</span>
                        <strong class="meta-name" id="slideName">{{ $hero?->name ?? 'سالن‌های منتخب' }}</strong>
                    </div>

                    <div class="hero-card-rating">
                        <span>★</span>
                        <b id="slideRating">{{ $hero?->reviews_avg_rating ? number_format((float) $hero->reviews_avg_rating, 1) : '—' }}</b>
                    </div>
                </div>

                <div class="side-dots" id="sideDots">
                    @foreach($featuredSalons as $index => $salon)
                        <button class="{{ $index === 0 ? 'active' : '' }}" type="button" data-index="{{ $index }}" aria-label="سالن {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                <div class="hero-link-row">
                    <div class="slide-mini-meta">
                        <span><b id="slideServices">{{ (int) ($hero?->services_count ?? 0) }}</b> خدمت</span>
                        <span>داده زنده از سالن</span>
                    </div>

                    <a
                        class="hero-link"
                        id="slideLink"
                        href="{{ $hero ? route('public.salons.show', ['salon' => $hero->slug]) : route('salons.discover') }}"
                    >
                        دیدن سالن <span>←</span>
                    </a>
                </div>
            </div>
        </section>

        <div class="marquee" aria-hidden="true">
            <div>
                <span>FIND</span><i></i><span>CHOOSE</span><i></i><span>BOOK</span><i></i>
                <span>FIND</span><i></i><span>CHOOSE</span><i></i><span>BOOK</span><i></i>
            </div>
        </div>

        <section class="section-block" id="services">
            <div class="section-head">
                <div>
                    <span class="section-kicker">01 / CATEGORIES</span>
                    <h2>دنبال چی هستی؟</h2>
                </div>
                <a href="{{ route('salons.discover') }}">همه خدمات ←</a>
            </div>

            <div class="category-grid">
                @foreach($categories as $category)
                    <a class="category-card" href="{{ route('salons.discover', ['q' => $category['query']]) }}">
                        <span class="category-index">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="category-mark">{{ $category['mark'] }}</span>
                        <strong>{{ $category['label'] }}</strong>
                        <span class="category-arrow">↙</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="section-block section-dark-alt">
            <div class="section-head">
                <div>
                    <span class="section-kicker">02 / POPULAR SERVICES</span>
                    <h2>سرویس‌های محبوب</h2>
                </div>
                <a href="{{ route('salons.discover') }}">کشف بیشتر ←</a>
            </div>

            <div class="service-grid">
                @forelse($popularServices as $service)
                    <a class="service-card" href="{{ route('salons.discover', ['service' => $service->name]) }}">
                        <div class="service-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="service-body">
                            <span>{{ $service->salon?->name ?? 'NOBAT' }}</span>
                            <strong>{{ $service->name }}</strong>
                            <small>{{ trim(collect([$service->salon?->city, $service->salon?->district])->filter()->implode('، ')) ?: 'در دسترس' }}</small>
                        </div>
                        <span class="service-arrow">←</span>
                    </a>
                @empty
                    <div class="empty-state">هنوز سرویسی برای نمایش ثبت نشده.</div>
                @endforelse
            </div>
        </section>

        <section class="section-block" id="lookbook">
            <div class="section-head">
                <div>
                    <span class="section-kicker">03 / LOOKBOOK</span>
                    <h2>کار واقعی، انتخاب واقعی</h2>
                </div>
                <a href="{{ route('salons.discover') }}">دیدن همه ←</a>
            </div>

            <div class="lookbook-grid">
                @forelse($lookbook as $item)
                    <a class="lookbook-card" href="{{ route('public.salons.show', ['salon' => $item['slug']]) }}">
                        <img src="{{ HomeController::mediaUrl($item['media']) }}" alt="{{ $item['title'] }}" loading="lazy">
                        <div class="lookbook-overlay">
                            <span>{{ $item['salon'] }}</span>
                            <strong>{{ $item['title'] }}</strong>
                        </div>
                    </a>
                @empty
                    <div class="empty-state">نمونه‌کاری برای نمایش وجود ندارد.</div>
                @endforelse
            </div>
        </section>

        <section class="section-block feature-strip">
            <div class="feature-strip-copy">
                <span class="section-kicker">04 / CHOOSE SMART</span>
                <h2>قبل از رزرو،<br><em>ببین.</em></h2>
                <p>نمونه‌کار واقعی، خدمات واقعی و مسیر مستقیم تا رزرو؛ همه در یک تجربه.</p>
            </div>

            <div class="feature-points">
                <div><span>01</span><strong>ببین</strong><small>تصویر و نمونه‌کار واقعی</small></div>
                <div><span>02</span><strong>انتخاب کن</strong><small>سالن و سرویس مناسب</small></div>
                <div><span>03</span><strong>نوبت بگیر</strong><small>بدون سرگردانی</small></div>
            </div>
        </section>

        <section class="section-block closing-block">
            <div class="closing-copy">
                <span class="section-kicker">05 / NOBAT</span>
                <h2>فقط نوبت نگیر.<br><em>درست انتخاب کن.</em></h2>
                <p>از روی تصویر، خدمات، موقعیت و تجربه دیگران انتخاب کن؛ بعد نوبتت را بگیر.</p>
                <a class="cta cta-primary" href="{{ route('salons.discover') }}">ورود به کشف <span>←</span></a>
            </div>

            <div class="closing-frame" aria-hidden="true">
                <span>NO<br>BAT</span>
                <i></i>
            </div>
        </section>

        <footer class="footer">
            <div>NOBAT — پیدا کن، انتخاب کن، نوبت بگیر.</div>
            <a href="{{ route('salons.discover') }}">کشف سالن‌ها ←</a>
        </footer>
    </div>
</main>

</body>
</html>
