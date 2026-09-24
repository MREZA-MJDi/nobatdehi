@php
    /*
    |--------------------------------------------------------------------------
    | Safe state
    |--------------------------------------------------------------------------
    */

    $filters = is_array($filters ?? null)
        ? $filters
        : [];

    $isSearchMode = $isSearchMode ?? false;

    $filters = array_merge([
        'q' => '',
        'type' => '',
        'service' => null,
        'province' => '',
        'city' => '',
        'location' => '',
        'district' => '',
        'sort' => 'recommended',
        'min_rating' => 0,
        'price_max' => '',
        'open_now' => false,
        'today' => false,
        'lat' => null,
        'lng' => null,
        'radius' => 15,
        ], $filters);

    /*
    |--------------------------------------------------------------------------
    | Result state
    |--------------------------------------------------------------------------
    */

    $salonsCount = method_exists($salons ?? null, 'total')
        ? $salons->total()
        : ($salons instanceof \Illuminate\Support\Collection ? $salons->count() : 0);

    $currentPage = method_exists($salons ?? null, 'currentPage')
        ? $salons->currentPage()
        : 1;

    $lastPage = method_exists($salons ?? null, 'lastPage')
        ? $salons->lastPage()
        : 1;

    $from = method_exists($salons ?? null, 'firstItem')
        ? ($salons->firstItem() ?? 0)
        : ($salonsCount > 0 ? 1 : 0);

    $to = method_exists($salons ?? null, 'lastItem')
        ? ($salons->lastItem() ?? 0)
        : $salonsCount;

    /*
    |--------------------------------------------------------------------------
    | Active filters
    |--------------------------------------------------------------------------
    */

    $hasActiveFilters =
        $filters['q'] !== ''
        || $filters['type'] !== ''
        || $filters['service'] !== null
        || $filters['province'] !== ''
        || $filters['city'] !== ''
        || $filters['location'] !== ''
        || $filters['district'] !== ''
        || (float) $filters['min_rating'] > 0
        || (
            is_numeric($filters['price_max'])
            && (float) $filters['price_max'] > 0
        )
        || (bool) $filters['open_now']
        || (bool) $filters['today']
        || (
            is_numeric($filters['lat'])
            && is_numeric($filters['lng'])
        )
        || $filters['sort'] !== 'recommended';

    $activeFilterCount = collect([
        $filters['q'] !== '',
        $filters['type'] !== '',
        $filters['service'] !== null && $filters['service'] !== '',
        $filters['province'] !== '',
        $filters['city'] !== '',
        $filters['location'] !== '',
        $filters['district'] !== '',
        (float) $filters['min_rating'] > 0,
        is_numeric($filters['price_max']) && (float) $filters['price_max'] > 0,
        (bool) $filters['open_now'],
        (bool) $filters['today'],
        is_numeric($filters['lat']) && is_numeric($filters['lng']),
        $filters['sort'] !== 'recommended',
    ])->filter()->count();

    /*
    |--------------------------------------------------------------------------
    | Dynamic result title
    |--------------------------------------------------------------------------
    */

    $resultTitle = 'سالن‌های موجود';

    $resultDescription = 'سالن مورد نظرت را پیدا کن و برای رزرو وارد پروفایلش شو.';

    if ($filters['q'] !== '') {
        $resultTitle = 'نتایج جستجو برای «' . $filters['q'] . '»';

        $resultDescription = 'سالن‌هایی که با عبارت جستجوی تو مطابقت دارند.';
    } elseif ($filters['service'] !== null && $filters['service'] !== '') {
        $resultTitle = 'سالن‌های این خدمت';

        $resultDescription = 'سالن‌هایی که این خدمت را ارائه می‌دهند.';
    } elseif ($filters['location'] !== '') {
        $resultTitle = 'نتایج «' . $filters['location'] . '»';

        $resultDescription = 'سالن‌های فعال در این شهر، منطقه یا محله.';
    } elseif ($filters['city'] !== '') {
        $resultTitle = 'سالن‌های ' . $filters['city'];

        $resultDescription = 'سالن‌های فعال در این محدوده.';
    } elseif ($filters['province'] !== '') {
        $resultTitle = 'سالن‌های ' . $filters['province'];

        $resultDescription = 'سالن‌های فعال این استان.';
    }

    /*
    |--------------------------------------------------------------------------
    | Image helper
    |--------------------------------------------------------------------------
    */

    $salonImage = function ($salon) {
        $path = $salon->cover_path
            ?? $salon->logo_path
            ?? null;

        if (! $path) {
            return null;
        }

        if (
            \Illuminate\Support\Str::startsWith(
                $path,
                [
                    'http://',
                    'https://',
                    '//',
                ]
            )
        ) {
            return $path;
        }

        return asset(
            'storage/' . ltrim($path, '/')
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Service label helper
    |--------------------------------------------------------------------------
    */

    $serviceLabel = function ($salon) {
        $services = $salon->services ?? collect();

        if (is_array($services)) {
            $services = collect($services);
        }

        return $services
            ->where('is_active', true)
            ->take(3)
            ->pluck('name')
            ->filter()
            ->values();
    };
@endphp

<section
    id="results"
    class="discover-results relative scroll-mt-28 py-16 sm:py-20 lg:py-24"
    aria-labelledby="discover-results-title"
>
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- ================================================================
             Header
        ================================================================= --}}
        <div class="mb-8 flex flex-col gap-6 lg:mb-10 lg:flex-row lg:items-end lg:justify-between">

            <div class="max-w-3xl">
                <div class="mb-3 flex items-center gap-3">
                    <span
                        class="inline-flex h-2 w-2 rounded-full bg-[var(--color-accent-500)]"
                        aria-hidden="true"
                    ></span>

                    <span class="text-xs font-bold tracking-[0.18em] text-[var(--color-content-muted)]">
                        DISCOVER
                    </span>
                </div>

                <h2
                    id="discover-results-title"
                    class="text-2xl font-black tracking-tight text-[var(--color-content)] sm:text-3xl lg:text-4xl"
                >
                    {{ $resultTitle }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-content-muted)] sm:text-base">
                    {{ $resultDescription }}
                </p>
            </div>

            <div class="flex items-center gap-3 self-start lg:self-auto">
                <div
                    class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 text-sm font-bold text-[var(--color-content)]"
                >
                    <svg
                        class="h-4 w-4 text-[var(--color-accent-500)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M20 20 16.5 16.5M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span>
                        {{ number_format($salonsCount) }}
                        سالن
                    </span>
                </div>

                @if ($hasActiveFilters)
                    <a
                        href="{{ route('salons.discover') }}"
                        data-discover-reset
                        class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-[var(--color-border)] bg-transparent px-4 text-sm font-bold text-[var(--color-content-muted)] transition hover:border-[var(--color-accent-500)] hover:text-[var(--color-accent-600)]"
                    >
                        حذف فیلترها

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M18 6 6 18M6 6l12 12"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </a>
                @endif
            </div>
        </div>


        {{-- ================================================================
             Filter trigger
        ================================================================= --}}
        <div class="discover-filter-bar">
            <button
                type="button"
                id="discoverFiltersOpen"
                class="discover-filter-trigger"
                aria-controls="discoverFiltersPanel"
                aria-expanded="false"
            >
                <span class="discover-filter-trigger__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 7h10M18 7h2M4 17h2M10 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="15.5" cy="7" r="2.2" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="8.5" cy="17" r="2.2" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </span>

                <span class="discover-filter-trigger__copy">
                    <strong>فیلتر و مرتب‌سازی</strong>
                    <small>
                        @if($activeFilterCount > 0)
                            {{ number_format($activeFilterCount) }} مورد فعال
                        @else
                            نتیجه را دقیق‌تر کن
                        @endif
                    </small>
                </span>

                @if($activeFilterCount > 0)
                    <span class="discover-filter-trigger__count">
                        {{ number_format($activeFilterCount) }}
                    </span>
                @endif

                <span class="discover-filter-trigger__arrow" aria-hidden="true">←</span>
            </button>

            @if($hasActiveFilters)
                <a
                    href="{{ route('salons.discover') }}"
                    data-discover-reset
                    class="discover-filter-reset"
                >
                    پاک کردن
                </a>
            @endif
        </div>

        <div
            id="discoverFiltersBackdrop"
            class="discover-filters-backdrop"
            hidden
            aria-hidden="true"
        ></div>

        <section
            id="discoverFiltersPanel"
            class="discover-results__filter"
            role="dialog"
            aria-modal="true"
            aria-labelledby="discoverFiltersTitle"
            aria-hidden="true"
            hidden
        >
            <div class="discover-filter-dialog">
                <header class="discover-filter-dialog__head">
                    <div>
                        <span>DISCOVER</span>
                        <h3 id="discoverFiltersTitle">فیلترها را انتخاب کن</h3>
                        <p>فقط چیزهایی را نگه دار که برای انتخابت مهم‌اند.</p>
                    </div>

                    <button
                        type="button"
                        class="discover-mobile-filter-close"
                        data-close-discover-filters
                        aria-label="بستن"
                    >
                        ×
                    </button>
                </header>

                <div class="discover-filter-dialog__body">
                    <form
                        method="GET"
                        action="{{ route('salons.discover') }}"
                        id="discoverFilterForm"
                        class="discover-filter-form"
                    >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                            <label class="block sm:col-span-2">
                                <span class="discover-filter-label">جستجو</span>

                                <input
                                    type="search"
                                    name="q"
                                    value="{{ $filters['q'] }}"
                                    placeholder="نام سالن، خدمت یا متخصص"
                                    autocomplete="off"
                                    class="discover-filter-input"
                                >
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">نوع</span>

                                <select name="type" class="discover-filter-input">
                                    <option value="">همه</option>
                                    <option value="salon" @selected($filters['type'] === 'salon')>سالن</option>
                                    <option value="barber" @selected($filters['type'] === 'barber')>متخصص</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">خدمت</span>

                                <select name="service" class="discover-filter-input">
                                    <option value="">همه خدمات</option>

                                    @foreach (($serviceOptions ?? collect()) as $serviceOptionName)
                                        @php $serviceOptionName = trim((string) $serviceOptionName); @endphp

                                        @if ($serviceOptionName !== '')
                                            <option
                                                value="{{ $serviceOptionName }}"
                                                @selected((string) $filters['service'] === $serviceOptionName)
                                            >
                                                {{ $serviceOptionName }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">استان</span>

                                <select name="province" id="discover-filter-province" class="discover-filter-input">
                                    <option value="">همه استان‌ها</option>

                                    @foreach (($provinces ?? collect()) as $province)
                                        <option value="{{ $province }}" @selected($filters['province'] === $province)>
                                            {{ $province }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">شهر</span>

                                <select name="city" id="discover-filter-city" class="discover-filter-input">
                                    <option value="">همه شهرها</option>

                                    @foreach (($cities ?? collect()) as $city)
                                        <option value="{{ $city }}" @selected($filters['city'] === $city)>
                                            {{ $city }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">منطقه / محله</span>

                                <input
                                    type="text"
                                    name="location"
                                    value="{{ $filters['location'] }}"
                                    placeholder="مثلاً سعادت‌آباد"
                                    autocomplete="address-level2"
                                    class="discover-filter-input"
                                >
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">حداقل امتیاز</span>

                                <select name="min_rating" class="discover-filter-input">
                                    <option value="0">هر امتیازی</option>
                                    <option value="3" @selected((float) $filters['min_rating'] === 3.0)>۳ به بالا</option>
                                    <option value="3.5" @selected((float) $filters['min_rating'] === 3.5)>۳.۵ به بالا</option>
                                    <option value="4" @selected((float) $filters['min_rating'] === 4.0)>۴ به بالا</option>
                                    <option value="4.5" @selected((float) $filters['min_rating'] === 4.5)>۴.۵ به بالا</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="discover-filter-label">حداکثر قیمت</span>

                                <input
                                    type="number"
                                    name="price_max"
                                    value="{{ $filters['price_max'] }}"
                                    min="0"
                                    step="1000"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۱۰۰۰۰۰۰"
                                    class="discover-filter-input"
                                >
                            </label>

                            <label class="block sm:col-span-2">
                                <span class="discover-filter-label">مرتب‌سازی</span>

                                <select name="sort" class="discover-filter-input">
                                    <option value="recommended" @selected($filters['sort'] === 'recommended')>پیشنهادی</option>
                                    <option value="rating" @selected($filters['sort'] === 'rating')>بالاترین امتیاز</option>
                                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>ارزان‌ترین</option>
                                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>گران‌ترین</option>
                                    <option value="distance" @selected($filters['sort'] === 'distance')>نزدیک‌ترین</option>
                                    <option value="newest" @selected($filters['sort'] === 'newest')>جدیدترین</option>
                                </select>
                            </label>
                        </div>

                        @if (is_numeric($filters['lat']) && is_numeric($filters['lng']))
                            <input type="hidden" name="lat" value="{{ $filters['lat'] }}">
                            <input type="hidden" name="lng" value="{{ $filters['lng'] }}">
                            <input type="hidden" name="radius" value="{{ $filters['radius'] }}">
                        @endif

                        <div class="discover-filter-options">
                            <label class="discover-filter-check">
                                <input type="checkbox" name="open_now" value="1" @checked($filters['open_now'])>
                                <span>
                                    <strong>الان باز</strong>
                                    <small>فقط سالن‌های باز را نشان بده</small>
                                </span>
                            </label>

                            <label class="discover-filter-check">
                                <input type="checkbox" name="today" value="1" @checked($filters['today'])>
                                <span>
                                    <strong>امروز نوبت آزاد دارد</strong>
                                    <small>بر اساس ظرفیت واقعی رزرو</small>
                                </span>
                            </label>

                            <button
                                type="button"
                                id="discoverUseLocation"
                                class="discover-filter-location"
                                data-discover-location
                            >
                                <span aria-hidden="true">⌖</span>
                                نزدیک من
                            </button>
                        </div>

                        <footer class="discover-filter-dialog__actions">
                            <button
                                type="button"
                                data-close-discover-filters
                                class="discover-filter-secondary"
                            >
                                انصراف
                            </button>

                            <button
                                type="submit"
                                class="discover-filter-primary"
                            >
                                نمایش نتایج
                                <span aria-hidden="true">←</span>
                            </button>
                        </footer>
                    </form>

                    <div
                        id="discoverFilterResults"
                        class="discover-filter-results"
                        aria-live="polite"
                        aria-busy="false"
                    >
                        <div class="discover-filter-results__idle">
                            <span aria-hidden="true">⌕</span>
                            <div>
                                <strong>هنوز فیلتری اعمال نشده</strong>
                                <small>بعد از «نمایش نتایج»، نتیجه همین‌جا ظاهر می‌شود.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================================================================
             Search state / count
        ================================================================= --}}
        

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-[var(--color-content-muted)]">
                    نمایش

                    <span class="font-bold text-[var(--color-content)]">
                        {{ number_format($from) }}
                    </span>

                    تا

                    <span class="font-bold text-[var(--color-content)]">
                        {{ number_format($to) }}
                    </span>

                    از

                    <span class="font-bold text-[var(--color-content)]">
                        {{ number_format($salonsCount) }}
                    </span>

                    سالن
                </div>

                @if ($lastPage > 1)
                    <div class="text-xs font-semibold text-[var(--color-content-muted)]">
                        صفحه {{ number_format($currentPage) }}
                        از
                        {{ number_format($lastPage) }}
                    </div>
                @endif
            </div>


            {{-- ============================================================
                 Cards
            ============================================================= --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                @foreach ($salons as $salon)
                    @php
                        $image = $salonImage($salon);

                        $rating = (float) ($salon->reviews_avg_rating ?? 0);

                        $reviewCount = (int) ($salon->reviews_count ?? 0);

                        $serviceNames = $serviceLabel($salon);

                        $distance = $salon->distance_km ?? null;

                        $locationParts = collect([
                            $salon->district ?? null,
                            $salon->city ?? null,
                        ])
                            ->filter()
                            ->values();
                    @endphp

                    <article
                        class="discover-result-card group overflow-hidden rounded-[30px] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_20px_70px_rgba(15,23,42,.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_90px_rgba(15,23,42,.10)]"
                    >
                        {{-- Image --}}
                        <a
                            href="{{ route('public.salons.show', $salon) }}"
                            class="relative block aspect-[16/10] overflow-hidden bg-[var(--color-background)]"
                        >
                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $salon->name }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--color-surface)] via-[var(--color-background)] to-[var(--color-surface)]">
                                    <div class="flex flex-col items-center gap-3 text-center">
                                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] text-xl font-black text-[var(--color-accent-600)]">
                                            {{ mb_substr($salon->name ?? 'N', 0, 1) }}
                                        </span>

                                        <span class="text-xs font-semibold text-[var(--color-content-muted)]">
                                            NOBAT
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/5 to-transparent opacity-70 transition duration-300 group-hover:opacity-90"></div>

                            {{-- Rating --}}
                            <div class="absolute left-4 top-4">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-black/45 px-3 py-2 text-xs font-bold text-white backdrop-blur-md">
                                    <svg
                                        class="h-3.5 w-3.5 text-amber-300"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.86L12 17.77l-6.18 3.23L7 14.14l-5-4.87 6.91-1.01L12 2Z"/>
                                    </svg>

                                    {{ number_format($rating, 1) }}

                                    @if ($reviewCount > 0)
                                        <span class="font-normal text-white/70">
                                            ({{ number_format($reviewCount) }})
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Distance --}}
                            @if (is_numeric($distance))
                                <div class="absolute right-4 top-4">
                                    <div class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-2 text-xs font-bold text-slate-800 backdrop-blur-md">
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M12 21s7-6.1 7-12A7 7 0 1 0 5 9c0 5.9 7 12 7 12Z"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            />
                                        </svg>

                                        {{ number_format((float) $distance, 1) }}
                                        کیلومتر
                                    </div>
                                </div>
                            @endif
                        </a>


                        {{-- Body --}}
                        <div class="p-5 sm:p-6">

                            {{-- Title --}}
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <a
                                        href="{{ route('public.salons.show', $salon) }}"
                                        class="line-clamp-1 text-lg font-black tracking-tight text-[var(--color-content)] transition hover:text-[var(--color-accent-600)]"
                                    >
                                        {{ $salon->name }}
                                    </a>

                                    @if ($locationParts->isNotEmpty())
                                        <div class="mt-1 flex items-center gap-1.5 text-xs font-medium text-[var(--color-content-muted)]">
                                            <svg
                                                class="h-3.5 w-3.5 shrink-0"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="M12 21s7-6.1 7-12A7 7 0 1 0 5 9c0 5.9 7 12 7 12Z"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                />
                                                <circle
                                                    cx="12"
                                                    cy="9"
                                                    r="2.5"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                />
                                            </svg>

                                            <span class="line-clamp-1">
                                                {{ $locationParts->join('، ') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if ($salon->code)
                                    <span class="shrink-0 rounded-lg bg-[var(--color-background)] px-2 py-1 text-[10px] font-bold text-[var(--color-content-muted)]">
                                        {{ $salon->code }}
                                    </span>
                                @endif
                            </div>


                            {{-- Address --}}
                            @if ($salon->address)
                                <p class="mt-4 line-clamp-2 text-sm leading-6 text-[var(--color-content-muted)]">
                                    {{ $salon->address }}
                                </p>
                            @endif


                            {{-- Services --}}
                            @if ($serviceNames->isNotEmpty())
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($serviceNames as $serviceName)
                                        <span class="rounded-full border border-[var(--color-border)] bg-[var(--color-background)] px-3 py-1.5 text-[11px] font-semibold text-[var(--color-content-muted)]">
                                            {{ $serviceName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif


                            {{-- Footer --}}
                            <div class="mt-6 flex items-center gap-3 border-t border-[var(--color-border)] pt-5">

                                <div class="flex min-w-0 flex-1 items-center gap-2 text-xs font-semibold text-[var(--color-content-muted)]">

                                    @if (($salon->services_count ?? 0) > 0)
                                        <span class="whitespace-nowrap">
                                            {{ number_format($salon->services_count) }}
                                            خدمت
                                        </span>
                                    @endif

                                    @if (($salon->barbers_count ?? 0) > 0)
                                        <span
                                            class="h-1 w-1 shrink-0 rounded-full bg-[var(--color-border)]"
                                            aria-hidden="true"
                                        ></span>

                                        <span class="whitespace-nowrap">
                                            {{ number_format($salon->barbers_count) }}
                                            متخصص
                                        </span>
                                    @endif
                                </div>

                                <a
                                    href="{{ route('public.salons.show', $salon) }}"
                                    class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-2xl bg-[var(--color-accent-600)] px-4 text-xs font-bold text-white transition hover:bg-[var(--color-accent-700)]"
                                >
                                    مشاهده سالن

                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M5 12h14M13 6l6 6-6 6"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach

            </div>


            {{-- ============================================================
                 Pagination
            ============================================================= --}}
            @if (method_exists($salons ?? null, 'hasPages') && $salons->hasPages())
                <div class="mt-10 flex justify-center">
                    <div class="discover-pagination w-full">
                        {{ $salons->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif

        @else

            {{-- ============================================================
                 Empty state
            ============================================================= --}}
            <div
                class="discover-results__empty relative overflow-hidden rounded-[32px] border border-[var(--color-border)] bg-[var(--color-surface)] px-6 py-16 text-center shadow-[0_20px_70px_rgba(15,23,42,.05)] sm:px-10 sm:py-20"
            >
                <div
                    class="pointer-events-none absolute inset-0 opacity-50"
                    aria-hidden="true"
                >
                    <div class="absolute right-[8%] top-[10%] h-40 w-40 rounded-full bg-[var(--color-accent-500)]/8 blur-3xl"></div>
                    <div class="absolute bottom-[8%] left-[12%] h-40 w-40 rounded-full bg-[var(--color-accent-600)]/8 blur-3xl"></div>
                </div>

                <div class="relative mx-auto max-w-xl">

                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[24px] border border-[var(--color-border)] bg-[var(--color-background)] text-[var(--color-accent-600)]">
                        <svg
                            class="h-9 w-9"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="m21 21-4.5-4.5M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                            <path
                                d="M8.5 8.5 13 13"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-6 text-2xl font-black tracking-tight text-[var(--color-content)]">
                        نتیجه‌ای پیدا نشد
                    </h3>

                    <p class="mt-3 text-sm leading-7 text-[var(--color-content-muted)] sm:text-base">
                        برای این ترکیب از فیلترها هنوز سالنی پیدا نکردیم.
                        فیلترها را کمی بازتر کن یا جستجوی جدیدی انجام بده.
                    </p>

                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                        <a
                            href="{{ route('salons.discover') }}"
                            data-discover-reset
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-[var(--color-accent-600)] px-6 text-sm font-bold text-white transition hover:bg-[var(--color-accent-700)]"
                        >
                            نمایش همه سالن‌ها

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 12h14M13 6l6 6-6 6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </a>

                        <a
                            href="#hero"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background)] px-6 text-sm font-bold text-[var(--color-content)] transition hover:border-[var(--color-accent-500)] hover:text-[var(--color-accent-600)]"
                        >
                            جستجوی دوباره
                        </a>

                    </div>
                </div>
            </div>

        @endif

    </div>
</section>
