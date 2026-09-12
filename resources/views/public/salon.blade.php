@extends('layouts.public')

@section('title', $salon->name . ' | رزرو نوبت آنلاین')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/salon.css') }}">
@endpush

@section('content')

    @php
        $barbers        = $salon->barbers ?? collect();
        $services       = $salon->services ?? collect();
        $posts          = $salon->posts ?? collect();
        $reviews        = $salon->reviews ?? collect();
        $firstBarber    = $barbers->first();
        $firstService   = $services->first();
        $todayDow       = (now()->dayOfWeek + 1) % 7;
        $todayHours     = ($salon->workingHours ?? collect())
                            ->where('day_of_week', $todayDow)
                            ->where('is_closed', false);
        $isOpenToday    = $todayHours->count() > 0;
    @endphp

    <div class="salon-page"
         id="salonPage"
         data-salon-id="{{ $salon->id }}"
         data-salon-name="{{ $salon->name }}"
         data-is-auth="{{ auth()->check() ? '1' : '0' }}"
         data-login-url="{{ route('login') }}"
         data-availability-url="{{ route('public.salons.booking.availability', $salon) }}"
         data-prepare-url="{{ route('public.salons.booking.prepare', $salon) }}"
         data-csrf="{{ csrf_token() }}">

        {{-- ═══════════ TOPBAR ═══════════ --}}
        <div class="topbar">
            <div class="topbar-in">
                <div class="brand">
                    <span class="dot">✦</span> {{ $salon->name }}
                </div>
                <nav class="topnav">
                    <a href="#gallery" class="keep">گالری</a>
                    <a href="#team">تیم</a>
                    <a href="#location">آدرس</a>
                    <a href="#about">درباره ما</a>
                    <button type="button" class="btn-diamond keep" data-open-booking>رزرو نوبت</button>
                </nav>
            </div>
        </div>

        <div class="wrap">

            {{-- ═══════════ HERO ═══════════ --}}
            <section class="hero reveal">
                <div class="cover">
                    @if($salon->cover_path)
                        <img src="{{ asset('storage/' . $salon->cover_path) }}" alt="">
                    @endif
                    <div class="cover-badge">
                        <span class="pulse-dot" @if(! $isOpenToday) style="background:#ff6b6b" @endif></span>
                        {{ $isOpenToday ? 'الان باز است' : 'امروز تعطیل' }}
                    </div>
                </div>

                <div class="profile-bar">
                    <div class="avatar">
                        @if($salon->logo_path)
                            <img src="{{ asset('storage/' . $salon->logo_path) }}" alt="">
                        @else
                            <span>{{ mb_substr($salon->name, 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="profile-info">
                        <div class="name-row">
                            <h1>{{ $salon->name }}</h1>
                            <span class="verified">✓</span>
                        </div>
                        <div class="tagline">
                            {{ Str::limit($salon->description ?? 'سالن تخصصی مو و استایل', 80) }}
                        </div>
                        <div class="stats">
                            <div class="stat">
                                <b>{{ number_format($postsCount) }}</b>
                                <span>نمونه‌کار</span>
                            </div>
                            <div class="stat">
                                <b>{{ number_format($reviewsCount) }}</b>
                                <span>نظر ثبت‌شده</span>
                            </div>
                            <div class="stat rate">
                                <b>★ {{ number_format($salon->reviews_avg_rating ?? 5, 1) }}</b>
                                <span>امتیاز مشتریان</span>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="button" class="btn-diamond" data-open-booking>رزرو نوبت</button>
                        <button type="button" class="btn-ghost" data-share>↗ اشتراک‌گذاری</button>
                    </div>
                </div>

                @if($salon->description)
                    <p class="bio">{{ $salon->description }}</p>
                @endif

                <div class="chips">
                    <span class="chip diamond">✦ {{ $barbersCount }} آرایشگر</span>
                    <span class="chip">{{ $servicesCount }} خدمت</span>
                    <span class="chip">مشاوره رایگان</span>
                    <span class="chip">محصولات اورجینال</span>
                </div>
            </section>

            {{-- ═══════════ BOOKING CTA ═══════════ --}}
            <section class="booking-cta reveal">
                <div class="bk-text">
                    <div class="eyebrow"><span>◆</span> نوبت‌دهی آنلاین</div>
                    <h2>وقتت رو همین حالا رزرو کن</h2>
                    <p>
                        بدون تماس، بدون انتظار. تقویم زنده‌ی سالن رو ببین، ساعت خالی رو
                        انتخاب کن و در چند ثانیه نوبتت رو قطعی کن.
                    </p>
                </div>
                <div class="bk-side">
                    <button type="button" class="btn-book" data-open-booking>
                        <span>📅</span> انتخاب زمان و رزرو
                    </button>
                    <div class="hint">لغو رایگان تا ۲ ساعت قبل</div>
                </div>
            </section>

            {{-- ═══════════ GALLERY ═══════════ --}}
            @if($posts->count())
                <section class="section reveal" id="gallery">
                    <div class="sec-head">
                        <h3>گالری سالن</h3>
                        <div class="line"></div>
                        <span class="count">{{ $postsCount }} پست</span>
                    </div>

                    <div class="tabs">
                        <button type="button" class="tab active" data-filter="all">همه</button>
                        <button type="button" class="tab" data-filter="reel">🎬 ریلز</button>
                        <button type="button" class="tab" data-filter="video">▶ ویدیو</button>
                        <button type="button" class="tab" data-filter="photo">🖼 عکس</button>
                        <button type="button" class="tab" data-filter="gif">✨ گیف</button>
                    </div>

                    <div class="gallery" id="galleryGrid">
                        @foreach($posts->take(12) as $post)
                            @php
                                $type = $post->type ?? 'photo';
                                $path = $post->path ?? $post->media_path ?? $post->image_path ?? null;
                                $typeLabel = match($type) {
                                    'reel'  => 'ریلز',
                                    'video' => 'ویدیو',
                                    'gif'   => 'گیف',
                                    default => 'عکس',
                                };
                            @endphp
                            <div class="tile" data-type="{{ $type }}">
                                <div class="media">
                                    @if($path && in_array($type, ['photo', 'gif']))
                                        <img src="{{ asset('storage/' . $path) }}" alt="{{ $post->title ?? '' }}">
                                    @elseif($path && in_array($type, ['video', 'reel']))
                                        <video src="{{ asset('storage/' . $path) }}" muted loop playsinline preload="metadata"></video>
                                    @else
                                        <div class="media g{{ ($loop->index % 8) + 1 }}"></div>
                                    @endif
                                </div>
                                <div class="ov"></div>
                                <span class="badge-type {{ $type }}">{{ $typeLabel }}</span>
                                @if(in_array($type, ['video', 'reel']))
                                    <div class="play">▶</div>
                                @endif
                                <div class="meta">
                                <span class="views">
                                    @if($post->barber ?? false)
                                        ✂ {{ $post->barber->name }}
                                    @elseif($post->service ?? false)
                                        ✦ {{ $post->service->name }}
                                    @endif
                                </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ═══════════ REVIEWS ═══════════ --}}
            @if($reviews->count())
                <section class="section reveal">
                    <div class="sec-head">
                        <h3>نظر مشتریان</h3>
                        <div class="line"></div>
                        <span class="count">{{ $reviewsCount }} نظر</span>
                    </div>

                    <div class="comments">
                        @foreach($reviews->take(6) as $review)
                            <div class="comment">
                                <div class="c-top">
                                    <div class="c-av">
                                        @if($review->customer?->avatar ?? false)
                                            <img src="{{ asset('storage/' . $review->customer->avatar) }}" alt="">
                                        @else
                                            {{ mb_substr($review->customer?->name ?? 'م', 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="c-name">{{ $review->customer?->name ?? 'مشتری' }}</div>
                                        <div class="c-date">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="c-stars">
                                        {{ str_repeat('★', $review->rating) }}<span style="color:#3a3a44">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="c-body">{{ $review->comment }}</p>
                                @endif
                                @if($review->booking?->service)
                                    <div class="c-service">خدمت: {{ $review->booking->service->name }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ═══════════ TEAM 2x2 ═══════════ --}}
            @if($barbers->count())
                <section class="section reveal" id="team">
                    <div class="sec-head">
                        <h3>تیم آرایشگران</h3>
                        <div class="line"></div>
                        <span class="count">{{ $barbersCount }} متخصص</span>
                    </div>

                    <div class="team-grid">
                        @foreach($barbers as $barber)
                            @php
                                $bReviews = $reviews->filter(fn ($r) => ($r->booking?->barber_id ?? null) === $barber->id);
                                $bRating  = $bReviews->count() ? round($bReviews->avg('rating'), 1) : 5.0;
                            @endphp
                            <div class="tcard">
                                <div class="t-head">
                                    <div class="t-av">
                                        @if($barber->image_path)
                                            <img src="{{ asset('storage/' . $barber->image_path) }}" alt="">
                                        @else
                                            {{ mb_substr($barber->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="t-name">{{ $barber->name }}</div>
                                        <div class="t-role">{{ $barber->specialty ?? 'آرایشگر' }}</div>
                                    </div>
                                    <div class="t-rating">
                                        <b>{{ number_format($bRating, 1) }}</b>
                                        <span>{{ number_format($bReviews->count()) }} نظر</span>
                                    </div>
                                </div>

                                @if($barber->bio)
                                    <p class="t-bio">{{ Str::limit($barber->bio, 130) }}</p>
                                @endif

                                <div class="t-foot">
                                    <button type="button"
                                            class="btn-diamond"
                                            data-open-booking
                                            data-barber-id="{{ $barber->id }}">
                                        رزرو با {{ explode(' ', $barber->name)[0] }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ═══════════ MAP + ABOUT ═══════════ --}}
            <section class="section reveal" id="location">
                <div class="sec-head">
                    <h3>آدرس و درباره ما</h3>
                    <div class="line"></div>
                </div>

                <div class="bottom-grid">
                    {{-- MAP --}}
                    <div class="map-card">
                        @if($mapsEmbedUrl)
                            <iframe src="{{ $mapsEmbedUrl }}" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen></iframe>
                            <div class="map-overlay">
                                <div class="map-addr">
                                    <b>{{ $salon->name }}</b>
                                    <span>{{ $fullAddress }}</span>
                                </div>
                                @if($googleMapsUrl)
                                    <a href="{{ $googleMapsUrl }}" target="_blank" rel="noopener"
                                       class="btn-ghost" style="padding:10px 18px">
                                        مسیریابی ↗
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="map-empty">موقعیت مکانی ثبت نشده</div>
                        @endif
                    </div>

                    {{-- ABOUT --}}
                    <div class="about-card" id="about">
                        <div class="sub">درباره ما</div>
                        <h3>{{ $salon->name }}</h3>
                        <p>
                            {{ $salon->description ?? 'ما به وقتِ تو احترام می‌ذاریم، به سلیقه‌ت گوش می‌دیم و با ابزار و محصولات حرفه‌ای کار می‌کنیم.' }}
                        </p>

                        <div class="feat">
                            <div><i>✂️</i> تجهیزات استریل</div>
                            <div><i>⏱</i> وقت‌شناسی دقیق</div>
                            <div><i>💎</i> محصولات اورجینال</div>
                            <div><i>🅿️</i> پارکینگ اختصاصی</div>
                        </div>

                        <div class="hours">
                        <span style="color:var(--muted)">
                            @if($todayHours->count())
                                امروز:
                                {{ $todayHours->pluck('start_time')->map(fn ($t) => substr($t, 0, 5))->join(' | ') }}
                            @else
                                امروز: تعطیل
                            @endif
                        </span>
                            <span class="open" @if(! $isOpenToday) style="color:var(--red)" @endif>
                            <span class="pulse-dot" @if(! $isOpenToday) style="background:#ff6b6b" @endif></span>
                            {{ $isOpenToday ? 'الان باز' : 'بسته' }}
                        </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ═══════════ RELATED SALONS ═══════════ --}}
            @if($relatedSalons->count())
                <section class="section reveal">
                    <div class="sec-head">
                        <h3>سالن‌های مشابه</h3>
                        <div class="line"></div>
                    </div>

                    <div class="related-grid">
                        @foreach($relatedSalons as $related)
                            <a href="{{ route('public.salons.show', $related) }}" class="related-card">
                                <div class="related-logo">
                                    @if($related->logo_path)
                                        <img src="{{ asset('storage/' . $related->logo_path) }}" alt="">
                                    @else
                                        <span>{{ mb_substr($related->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="related-name">{{ $related->name }}</div>
                                <div class="related-meta">
                                    <span>★ {{ number_format($related->reviews_avg_rating ?? 5, 1) }}</span>
                                    @if($related->services_count ?? false)
                                        <span>{{ $related->services_count }} خدمت</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <div style="height:60px"></div>
        </div>

        {{-- ═══════════ FLOATING BOOK (mobile) ═══════════ --}}
        <div class="float-book">
            <button type="button" data-open-booking>📅 رزرو نوبت</button>
        </div>

        {{-- ═══════════ BOOKING MODAL ═══════════ --}}
        <div class="modal-back" id="bookingModal">
            <div class="modal">
                <button type="button" class="modal-close" data-close-booking aria-label="بستن">✕</button>

                <div id="modalMain">
                    <div class="m-head">
                        <div class="eyebrow">◆ رزرو نوبت</div>
                        <h2>زمانت رو انتخاب کن</h2>
                        <p>آرایشگر و خدمت رو انتخاب کن، بعد تقویم زنده‌ی سالن رو ببین.</p>
                    </div>

                    {{-- Filters --}}
                    <div class="m-filters">
                        <label class="field">
                            <span>آرایشگر</span>
                            <select id="filterBarber">
                                @foreach($barbers as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->name }}{{ $b->specialty ? ' — ' . $b->specialty : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>خدمت</span>
                            <select id="filterService">
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} ({{ $s->duration_minutes }} دقیقه{{ $s->price ? ' — ' . number_format($s->price) . ' تومان' : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="m-grid">
                        {{-- Calendar --}}
                        <div>
                            <div class="cal-head">
                                <b id="calTitle">—</b>
                                <div class="cal-nav">
                                    <button type="button" data-cal-shift="1" aria-label="ماه بعد">‹</button>
                                    <button type="button" data-cal-shift="-1" aria-label="ماه قبل">›</button>
                                </div>
                            </div>
                            <div class="cal-week">
                                <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                            </div>
                            <div class="cal-days" id="calDays"></div>

                            <div class="legend">
                                <i><span class="sq" style="background:linear-gradient(135deg,#ffffff,#c5d2e0)"></span> انتخاب‌شده</i>
                                <i><span class="sq" style="background:rgba(255,255,255,.06)"></span> موجود</i>
                                <i><span class="sq" style="background:#2a2a32"></span> گذشته</i>
                            </div>
                        </div>

                        {{-- Slots --}}
                        <div>
                            <div class="slots-title">
                                <span>ساعت‌های خالی</span>
                                <small id="slotDate">— یک روز انتخاب کن</small>
                            </div>
                            <div class="slots" id="slots">
                                <div class="slots-msg">اول از تقویم یک روز انتخاب کن</div>
                            </div>
                        </div>
                    </div>

                    <div class="m-foot">
                        <div class="m-summary">
                            <div class="row"><span>آرایشگر</span><b id="sumBarber">—</b></div>
                            <div class="row"><span>خدمت</span><b id="sumService">—</b></div>
                            <div class="row"><span>زمان</span><b id="sumTime">—</b></div>
                        </div>
                        <button type="button" class="btn-confirm" id="confirmBtn" disabled>
                            تأیید رزرو
                        </button>
                    </div>
                </div>

                {{-- Success --}}
                <div class="success" id="successBox">
                    <div class="tick">✓</div>
                    <h3>نوبتت ثبت شد!</h3>
                    <p id="successText">—</p>
                    <button type="button" class="btn-diamond" style="margin-top:22px" data-close-booking>
                        باشه، ممنون
                    </button>
                </div>
            </div>
        </div>

        <footer>
            ساخته‌شده با <b>✦</b> برای <b>{{ $salon->name }}</b> — تمام حقوق محفوظ است.
        </footer>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/customer.js') }}" defer></script>
@endpush
