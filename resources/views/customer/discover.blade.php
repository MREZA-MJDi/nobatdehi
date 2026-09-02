
@extends('layouts.app')

@section('title', 'پیدا کردن سالن')

@section(
    'meta_description',
    'سالن یا آرایشگر موردنظر خود را با نام، کد اختصاصی یا موقعیت پیدا کنید و نوبت بگیرید.'
)

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;

        $persianDigits = [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ];

        $activeSalonCount = $activeSalonCount ?? 0;
        $activeBarberCount = $activeBarberCount ?? 0;
        $bookingCount = $bookingCount ?? 0;

        $barbers = $barbers ?? collect();
        $mapSalons = $mapSalons ?? collect();

        $query = trim((string) ($query ?? request('q', '')));
        $code = trim((string) ($code ?? request('code', '')));

        $currentType = $type ?? request('type', 'all');

        if (!in_array($currentType, ['all', 'salon', 'barber'], true)) {
            $currentType = 'all';
        }

        $salonCountText = strtr(
            number_format($activeSalonCount),
            $persianDigits
        );

        $barberCountText = strtr(
            number_format($activeBarberCount),
            $persianDigits
        );

        $bookingCountText = strtr(
            number_format($bookingCount),
            $persianDigits
        );

        /*
        |--------------------------------------------------------------------------
        | Map Data
        |--------------------------------------------------------------------------
        */

        $mapData = $mapSalons
            ->map(function ($salon) {

                $lat = (float) $salon->latitude;
                $lng = (float) $salon->longitude;

                return [
                    'id' => $salon->id,
                    'name' => $salon->name,
                    'slug' => $salon->slug,
                    'lat' => $lat,
                    'lng' => $lng,
                    'city' => $salon->city,
                    'district' => $salon->district,

                    'url' => route(
                        'public.salons.show',
                        $salon
                    ),

                    'booking_url' => route(
                        'public.salons.booking.create',
                        $salon
                    ),

                    'maps_url' =>
                        'https://www.google.com/maps/dir/?api=1&destination=' .
                        urlencode($lat . ',' . $lng),
                ];
            })
            ->values();

        $firstMapSalon = $mapData->first();
    @endphp


    <div
        x-data="{
            mode: @js($currentType),

            recentOpen: false,
            mapOpen: false,

            selectedMapSalon: @js($firstMapSalon),

            setMode(value) {
                this.mode = value;
            },

            selectMapSalon(salon) {
                this.selectedMapSalon = salon;
            },

            openMap(salon = null) {
                if (salon) {
                    this.selectedMapSalon = salon;
                }

                this.mapOpen = true;
            },

            closeMap() {
                this.mapOpen = false;
            },

            saveRecentSearch(value) {
                const normalized = value?.trim();

                if (!normalized) {
                    return;
                }

                const key = 'nobatdehi_recent_searches';

                let items = [];

                try {
                    items = JSON.parse(
                        localStorage.getItem(key) || '[]'
                    );

                    if (!Array.isArray(items)) {
                        items = [];
                    }
                } catch (e) {
                    items = [];
                }

                items = [
                    normalized,
                    ...items.filter(
                        item => item !== normalized
                    )
                ].slice(0, 6);

                localStorage.setItem(
                    key,
                    JSON.stringify(items)
                );

                this.recentOpen = true;
            },

            getRecentSearches() {
                try {
                    const value = JSON.parse(
                        localStorage.getItem(
                            'nobatdehi_recent_searches'
                        ) || '[]'
                    );

                    return Array.isArray(value)
                        ? value
                        : [];

                } catch (e) {
                    return [];
                }
            },

            useCurrentLocation() {
                if (!navigator.geolocation) {
                    toast.warning(
                        'مرورگر شما از مکان‌یابی پشتیبانی نمی‌کند.'
                    );

                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    position => {

                        const lat =
                            position.coords.latitude;

                        const lng =
                            position.coords.longitude;

                        window.location.href =
                            '{{ route('salons.discover') }}'
                            + '?lat='
                            + encodeURIComponent(lat)
                            + '&lng='
                            + encodeURIComponent(lng);

                    },

                    () => {
                        toast.error(
                            'دسترسی به موقعیت مکانی امکان‌پذیر نشد.'
                        );
                    },

                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            }
        }"
        class="app-container"
    >


        {{-- ============================================================
            HERO
        ============================================================= --}}

        <section class="discover-hero relative overflow-hidden">

            <div
                class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-accent-500/10 blur-3xl"
            ></div>


            <div
                class="pointer-events-none absolute -right-20 bottom-0 h-64 w-64 rounded-full bg-primary-500/5 blur-3xl"
            ></div>


            <div class="relative z-10">

                {{-- Eyebrow --}}

                <div class="discover-eyebrow">

                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-accent-100 text-accent-700"
                    >

                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>

                    </span>

                    نزدیک‌ترین جای خوب برای نوبتت را پیدا کن

                </div>


                {{-- Heading --}}

                <h1 class="discover-title text-balance">

                    سالن یا آرایشگر
                    <span>موردنظرت</span>
                    اینجاست.

                </h1>


                <p class="discover-description">

                    اسم سالن، نام آرایشگر یا کد اختصاصی سالن را جستجو کن،
                    بعد خدمات، موقعیت و زمان مناسب را برای رزرو ببین.

                </p>


                {{-- ====================================================
                    SEARCH PANEL
                ===================================================== --}}

                <div class="search-panel">


                    {{-- Search modes --}}

                    <div class="mb-4 flex items-center gap-1 overflow-x-auto pb-1">

                        @foreach([
                            'all' => 'همه',
                            'salon' => 'سالن‌ها',
                            'barber' => 'آرایشگرها',
                        ] as $value => $label)

                            <button
                                type="button"
                                @click="setMode('{{ $value }}')"
                                class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition"
                                :class="
                                    mode === '{{ $value }}'
                                        ? 'bg-primary-950 text-white'
                                        : 'text-content-muted hover:bg-primary-100 hover:text-content'
                                "
                            >
                                {{ $label }}
                            </button>

                        @endforeach

                    </div>


                    {{-- Search form --}}

                    <form
                        action="{{ route('salons.discover') }}"
                        method="GET"
                        @submit="
                            saveRecentSearch(
                                $el.querySelector('[name=q]')?.value
                                || $el.querySelector('[name=code]')?.value
                            )
                        "
                    >

                        <input
                            type="hidden"
                            name="type"
                            :value="mode === 'all' ? '' : mode"
                        >


                        <div class="search-grid">


                            {{-- Search --}}

                            <div class="search-field">

                                <span class="search-field-icon">

                                    <svg
                                        width="19"
                                        height="19"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="11" cy="11" r="6.5" />
                                        <path d="m16 16 4.5 4.5" />
                                    </svg>

                                </span>


                                <input
                                    type="search"
                                    name="q"
                                    value="{{ $query }}"
                                    class="search-input"
                                    placeholder="اسم سالن یا آرایشگر..."
                                    autocomplete="off"
                                >

                            </div>


                            {{-- Salon Code --}}

                            <div class="search-field">

                                <span class="search-field-icon">

                                    <svg
                                        width="19"
                                        height="19"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="4"
                                            y="4"
                                            width="16"
                                            height="16"
                                            rx="2"
                                        />

                                        <path d="M8 8h3v3H8z" />
                                        <path d="M13 13h3v3h-3z" />
                                        <path d="M14 8h2" />
                                        <path d="M8 16h2" />

                                    </svg>

                                </span>


                                <input
                                    type="search"
                                    name="code"
                                    value="{{ $code }}"
                                    class="search-input"
                                    placeholder="کد سالن"
                                    autocomplete="off"
                                    dir="ltr"
                                >

                            </div>


                            {{-- Submit --}}

                            <button
                                type="submit"
                                class="btn btn-accent btn-lg search-submit"
                            >

                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle cx="11" cy="11" r="6.5" />
                                    <path d="m16 16 4.5 4.5" />
                                </svg>

                                جستجو

                            </button>

                        </div>

                    </form>


                    {{-- Quick Actions --}}

                    <div class="mt-3 flex flex-wrap gap-2">


                        {{-- Near Me --}}

                        <button
                            type="button"
                            class="filter-chip"
                            @click="useCurrentLocation()"
                        >

                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="12" cy="12" r="8" />
                                <circle cx="12" cy="12" r="2" />
                                <path d="M12 4v2" />
                                <path d="M12 18v2" />
                                <path d="M4 12h2" />
                                <path d="M18 12h2" />
                            </svg>

                            نزدیک من

                        </button>


                        {{-- Recent Searches --}}

                        <button
                            type="button"
                            class="filter-chip"
                            @click="recentOpen = !recentOpen"
                        >

                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="12" cy="12" r="8" />
                                <path d="M12 8v4l2.5 2" />
                            </svg>

                            جستجوهای اخیر

                        </button>

                    </div>


                    {{-- Recent Searches --}}

                    <div
                        x-cloak
                        x-show="recentOpen"
                        x-transition
                        x-data="{ items: getRecentSearches() }"
                        class="mt-3 rounded-2xl border border-border bg-surface-soft p-3"
                    >

                        <div class="mb-2 flex items-center justify-between gap-3">

                            <div class="text-xs font-black text-content">
                                جستجوهای اخیر
                            </div>


                            <button
                                type="button"
                                class="text-[10px] font-bold text-content-faint hover:text-content"
                                @click="
                                    localStorage.removeItem('nobatdehi_recent_searches');
                                    items = [];
                                "
                            >
                                پاک کردن
                            </button>

                        </div>


                        <div class="flex flex-wrap gap-2">

                            <template
                                x-for="item in items"
                                :key="item"
                            >

                                <a
                                    :href="
                                        '{{ route('salons.discover') }}?q='
                                        + encodeURIComponent(item)
                                    "
                                    class="rounded-xl border border-border bg-surface px-3 py-2 text-[10px] font-bold text-content-muted transition hover:border-accent-300 hover:text-accent-600"
                                    x-text="item"
                                ></a>

                            </template>


                            <template x-if="!items.length">

                                <span class="text-[10px] text-content-faint">
                                    هنوز جستجویی ثبت نشده است.
                                </span>

                            </template>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ============================================================
            STATS
        ============================================================= --}}

        <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">


            {{-- Salons --}}

            <div class="rounded-2xl border border-border bg-surface p-4 shadow-soft">

                <div class="mb-3 flex items-center justify-between">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-accent-600">

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>

                    </div>

                </div>


                <div class="text-xl font-black text-content">
                    {{ $salonCountText }}
                </div>


                <div class="mt-1 text-xs text-content-muted">
                    سالن فعال
                </div>

            </div>


            {{-- Barbers --}}

            <div class="rounded-2xl border border-border bg-surface p-4 shadow-soft">

                <div class="mb-3 flex items-center justify-between">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 text-content-soft">

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="8" r="3.5" />
                            <path d="M5 21c.7-4 3.1-6 7-6s6.3 2 7 6" />
                        </svg>

                    </div>

                </div>


                <div class="text-xl font-black text-content">
                    {{ $barberCountText }}
                </div>


                <div class="mt-1 text-xs text-content-muted">
                    آرایشگر فعال
                </div>

            </div>


            {{-- Bookings --}}

            <div class="rounded-2xl border border-border bg-surface p-4 shadow-soft">

                <div class="mb-3 flex items-center justify-between">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="m5 12 4 4L19 6" />
                        </svg>

                    </div>

                </div>


                <div class="text-xl font-black text-content">
                    {{ $bookingCountText }}
                </div>


                <div class="mt-1 text-xs text-content-muted">
                    نوبت ثبت‌شده
                </div>

            </div>

        </section>


        {{-- ============================================================
            RESULTS
        ============================================================= --}}

        <section class="discover-layout">


            {{-- ========================================================
                RESULT COLUMN
            ========================================================= --}}

            <div class="results-column">


                {{-- Header --}}

                <div class="results-header">

                    <div>

                        <div class="flex flex-wrap items-center gap-2">

                            <h2 class="section-title">

                                @if($query || $code)

                                    نتایج جستجو

                                @elseif($currentType === 'barber')

                                    آرایشگرها

                                @elseif($currentType === 'salon')

                                    سالن‌ها

                                @else

                                    سالن‌های فعال

                                @endif

                            </h2>


                            @if($query || $code)

                                <span class="rounded-full bg-accent-100 px-2.5 py-1 text-[10px] font-bold text-accent-700">
                                    جستجو
                                </span>

                            @endif

                        </div>


                        <p class="results-count mt-1">

                            @if(isset($salons))

                                {{ strtr(
                                    number_format($salons->total()),
                                    $persianDigits
                                ) }}

                                نتیجه

                            @else

                                نتایج موجود

                            @endif

                        </p>

                    </div>

                </div>


                {{-- ====================================================
                    BARBER RESULTS
                ===================================================== --}}

                @if(
                    ($currentType === 'barber' || $currentType === 'all') &&
                    $barbers->isNotEmpty()
                )

                    <section class="mb-6">

                        <div class="mb-3 flex items-end justify-between gap-3">

                            <div>

                                <div class="text-[10px] font-black tracking-wider text-accent-600">
                                    BARBERS
                                </div>

                                <h3 class="mt-1 text-base font-black text-content">
                                    آرایشگرها
                                </h3>

                            </div>

                        </div>


                        <div class="grid gap-3 sm:grid-cols-2">


                            @foreach($barbers as $barber)

                                @php
                                    $barberSalon = $barber->salon;
                                @endphp

                                @if($barberSalon)

                                    <a
                                        href="{{ route(
                                            'public.salons.show',
                                            $barberSalon
                                        ) }}#barbers"
                                        class="group rounded-2xl border border-border bg-surface p-4 transition duration-200 hover:-translate-y-0.5 hover:border-accent-300 hover:shadow-soft"
                                    >

                                        <div class="flex items-center gap-3">

                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary-100 text-sm font-black text-content-soft">

                                                @if($barber->image_path)

                                                    <img
                                                        src="{{ Storage::url($barber->image_path) }}"
                                                        alt="{{ $barber->name }}"
                                                        class="h-full w-full object-cover"
                                                    >

                                                @else

                                                    {{ mb_substr(
                                                        $barber->name,
                                                        0,
                                                        1
                                                    ) }}

                                                @endif

                                            </div>


                                            <div class="min-w-0 flex-1">

                                                <div class="truncate text-sm font-black text-content group-hover:text-accent-700">
                                                    {{ $barber->name }}
                                                </div>


                                                <div class="mt-1 truncate text-[10px] text-content-muted">

                                                    {{ $barber->specialty ?: 'آرایشگر سالن' }}

                                                </div>


                                                <div class="mt-1 flex items-center gap-1 text-[10px] text-content-faint">

                                                    <span>
                                                        {{ $barberSalon->name }}
                                                    </span>

                                                    @if($barberSalon->city)

                                                        <span>•</span>

                                                        <span>
                                                            {{ $barberSalon->city }}
                                                        </span>

                                                    @endif

                                                </div>

                                            </div>


                                            <svg
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                class="shrink-0 text-content-faint transition group-hover:text-accent-600"
                                            >
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>

                                        </div>

                                    </a>

                                @endif

                            @endforeach

                        </div>

                    </section>

                @endif


                {{-- ====================================================
                    SALON RESULTS
                ===================================================== --}}

                @if(
                    $currentType !== 'barber' &&
                    $salons->count()
                )

                    <div class="salon-grid">

                        @foreach($salons as $salon)

                            @php

                                $coverUrl = $salon->cover_path
                                    ? Storage::url($salon->cover_path)
                                    : null;

                                $logoUrl = $salon->logo_path
                                    ? Storage::url($salon->logo_path)
                                    : null;

                                $hasLocation = (
                                    $salon->latitude !== null &&
                                    $salon->longitude !== null
                                );

                                $mapsUrl = $hasLocation
                                    ? 'https://www.google.com/maps/dir/?api=1&destination=' .
                                        urlencode(
                                            $salon->latitude . ',' .
                                            $salon->longitude
                                        )
                                    : null;

                            @endphp


                            <article
                                class="salon-card card-hover group overflow-hidden"
                            >


                                {{-- Cover --}}

                                <div class="relative h-44 overflow-hidden bg-primary-100">

                                    @if($coverUrl)

                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $salon->name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >

                                    @else

                                        <div
                                            class="h-full w-full"
                                            style="
                                                background:
                                                radial-gradient(
                                                circle at 80% 20%,
                                            {{ $salon->primary_color ?: '#6757E8' }}66,
                                                transparent 45%
                                                ),
                                                linear-gradient(
                                                135deg,
                                                #18181b,
                                                #3f3f46
                                                );
                                                "
                                        ></div>

                                    @endif


                                    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/65 to-transparent"></div>


                                    {{-- Status --}}

                                    <div class="absolute left-3 top-3">

                                        @if($salon->is_active)

                                            <span class="rounded-full border border-white/20 bg-black/35 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md">
                                                فعال
                                            </span>

                                        @endif

                                    </div>


                                    {{-- Logo --}}

                                    <div class="absolute right-4 bottom-[-1.5rem] flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border-4 border-surface bg-primary-950 text-lg font-black text-white shadow-lg">

                                        @if($logoUrl)

                                            <img
                                                src="{{ $logoUrl }}"
                                                alt="{{ $salon->name }}"
                                                class="h-full w-full bg-white object-contain"
                                            >

                                        @else

                                            {{ mb_substr(
                                                $salon->name,
                                                0,
                                                1
                                            ) }}

                                        @endif

                                    </div>

                                </div>


                                {{-- Content --}}

                                <div class="salon-content pt-8">


                                    {{-- Title --}}

                                    <div class="salon-topline">

                                        <div class="min-w-0">

                                            <h3 class="salon-name group-hover:text-accent-700">
                                                {{ $salon->name }}
                                            </h3>


                                            <p class="salon-type">
                                                سالن زیبایی
                                            </p>

                                        </div>

                                    </div>


                                    {{-- Location --}}

                                    @if($salon->city || $salon->district)

                                        <div class="salon-meta mt-3">

                                            <div class="salon-meta-item">

                                                <svg
                                                    width="14"
                                                    height="14"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                                    <circle cx="12" cy="10" r="2.5" />
                                                </svg>


                                                <span>

                                                    {{ $salon->city ?: '—' }}

                                                    @if($salon->district)

                                                        ، {{ $salon->district }}

                                                    @endif

                                                </span>

                                            </div>

                                        </div>

                                    @endif


                                    {{-- Phone --}}

                                    @if($salon->phone)

                                        <div class="salon-meta">

                                            <div class="salon-meta-item">

                                                <svg
                                                    width="14"
                                                    height="14"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7c.1 1 .3 2 .7 2.9a2 2 0 0 1-.5 2.1L9 10.9a16 16 0 0 0 4.1 4.1l1.2-1.3a2 2 0 0 1 2.1-.5c.9.4 1.9.6 2.9.7A2 2 0 0 1 22 16.9Z" />
                                                </svg>


                                                <span dir="ltr">
                                                    {{ $salon->phone }}
                                                </span>

                                            </div>

                                        </div>

                                    @endif


                                    {{-- Counters --}}

                                    <div class="mt-4 flex flex-wrap gap-2">

                                        @if(isset($salon->barbers_count))

                                            <span class="rounded-xl bg-primary-100 px-3 py-2 text-[10px] font-bold text-content-muted">

                                                {{ strtr(
                                                    (string) $salon->barbers_count,
                                                    $persianDigits
                                                ) }}

                                                آرایشگر

                                            </span>

                                        @endif


                                        @if(isset($salon->services_count))

                                            <span class="rounded-xl bg-primary-100 px-3 py-2 text-[10px] font-bold text-content-muted">

                                                {{ strtr(
                                                    (string) $salon->services_count,
                                                    $persianDigits
                                                ) }}

                                                خدمت

                                            </span>

                                        @endif

                                    </div>


                                    {{-- Actions --}}

                                    <div class="mt-4 grid grid-cols-2 gap-2">


                                        <a
                                            href="{{ route(
                                                'public.salons.show',
                                                $salon
                                            ) }}"
                                            class="btn btn-secondary btn-sm"
                                        >
                                            مشاهده سالن
                                        </a>


                                        <a
                                            href="{{ route(
                                                'public.salons.booking.create',
                                                $salon
                                            ) }}"
                                            class="btn btn-accent btn-sm"
                                        >
                                            رزرو نوبت
                                        </a>

                                    </div>


                                    {{-- Secondary actions --}}

                                    <div class="mt-2 flex items-center justify-between gap-2">

                                        <code
                                            class="salon-code"
                                            dir="ltr"
                                        >
                                            {{ $salon->code }}
                                        </code>


                                        @if($mapsUrl)

                                            <button
                                                type="button"
                                                @click="
                                                    openMap({
                                                        id: {{ $salon->id }},
                                                        name: @js($salon->name),
                                                        lat: {{ (float) $salon->latitude }},
                                                        lng: {{ (float) $salon->longitude }},
                                                        url: @js(route('public.salons.show', $salon)),
                                                        booking_url: @js(route('public.salons.booking.create', $salon)),
                                                        maps_url: @js($mapsUrl)
                                                    })
                                                "
                                                class="inline-flex items-center gap-1.5 text-[10px] font-bold text-content-muted transition hover:text-accent-600"
                                            >

                                                <svg
                                                    width="14"
                                                    height="14"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                                    <circle cx="12" cy="10" r="2.5" />
                                                </svg>

                                                نقشه

                                            </button>

                                        @endif

                                    </div>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    {{-- Pagination --}}

                    @if(method_exists($salons, 'links'))

                        <div class="mt-6">
                            {{ $salons->links() }}
                        </div>

                    @endif


                @elseif($currentType !== 'barber')

                    <div class="rounded-3xl border border-dashed border-border bg-surface p-10 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-content-muted">

                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="11" cy="11" r="6.5" />
                                <path d="m16 16 4.5 4.5" />
                            </svg>

                        </div>


                        <h2 class="mt-4 text-base font-black text-content">
                            نتیجه‌ای پیدا نشد
                        </h2>


                        <p class="mt-2 text-xs leading-6 text-content-muted">
                            عبارت جستجو یا کد سالن را تغییر بده و دوباره جستجو کن.
                        </p>


                        <a
                            href="{{ route('salons.discover') }}"
                            class="btn btn-secondary mt-5"
                        >
                            نمایش همه سالن‌ها
                        </a>

                    </div>

                @endif

            </div>


            {{-- ========================================================
                DESKTOP MAP
            ========================================================= --}}

            <aside class="map-column hidden md:block">

                <div class="map-card sticky top-24 overflow-hidden">

                    @if($mapData->count())

                        <div class="relative min-h-[36rem] overflow-hidden rounded-3xl border border-border bg-surface">


                            {{-- Real Google Map --}}

                            <iframe
                                :key="selectedMapSalon?.lat + '-' + selectedMapSalon?.lng"
                                :src="
                                    selectedMapSalon
                                        ? 'https://www.google.com/maps?q='
                                            + selectedMapSalon.lat
                                            + ','
                                            + selectedMapSalon.lng
                                            + '&z=15&output=embed'
                                        : ''
                                "
                                title="موقعیت سالن روی Google Maps"
                                class="absolute inset-0 h-full w-full border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>


                            {{-- Top info --}}

                            <div class="absolute inset-x-3 top-3">

                                <div class="rounded-2xl border border-white/60 bg-white/88 p-3 shadow-float backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/90">

                                    <div class="flex items-center justify-between gap-3">

                                        <div class="min-w-0">

                                            <div class="text-[10px] font-black tracking-wider text-accent-600">
                                                LOCATION
                                            </div>


                                            <div
                                                class="mt-1 truncate text-xs font-black text-zinc-950 dark:text-white"
                                                x-text="selectedMapSalon?.name || 'موقعیت سالن'"
                                            ></div>

                                        </div>


                                        <a
                                            x-show="selectedMapSalon?.maps_url"
                                            :href="selectedMapSalon?.maps_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-secondary btn-sm shrink-0"
                                        >
                                            مسیر
                                        </a>

                                    </div>

                                </div>

                            </div>


                            {{-- Salon map list --}}

                            <div class="absolute inset-x-3 bottom-3">

                                <div class="max-h-48 overflow-y-auto rounded-2xl border border-white/60 bg-white/88 p-2 shadow-float backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/90">

                                    @foreach($mapData as $mapSalon)

                                        <button
                                            type="button"
                                            @click="
                                                selectMapSalon({
                                                    id: {{ $mapSalon['id'] }},
                                                    name: @js($mapSalon['name']),
                                                    lat: {{ $mapSalon['lat'] }},
                                                    lng: {{ $mapSalon['lng'] }},
                                                    url: @js($mapSalon['url']),
                                                    booking_url: @js($mapSalon['booking_url']),
                                                    maps_url: @js($mapSalon['maps_url'])
                                                })
                                            "
                                            class="flex w-full items-center justify-between gap-3 rounded-xl p-3 text-right transition hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                        >

                                            <div class="min-w-0">

                                                <div class="truncate text-xs font-black text-zinc-950 dark:text-white">
                                                    {{ $mapSalon['name'] }}
                                                </div>


                                                <div class="mt-1 truncate text-[10px] text-zinc-500 dark:text-zinc-400">

                                                    {{ collect([
                                                        $mapSalon['city'],
                                                        $mapSalon['district'],
                                                    ])->filter()->join('، ') }}

                                                </div>

                                            </div>


                                            <span class="shrink-0 text-[10px] font-bold text-accent-600">
                                                انتخاب
                                            </span>

                                        </button>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    @else

                        <div class="flex min-h-[36rem] items-center justify-center rounded-3xl border border-dashed border-border bg-surface p-8 text-center">

                            <div>

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-content-muted">

                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>

                                </div>


                                <h3 class="mt-4 text-sm font-black text-content">
                                    نقشه آماده است
                                </h3>


                                <p class="mt-2 text-[10px] leading-6 text-content-muted">
                                    به‌محض ثبت مختصات سالن‌ها، موقعیت واقعی آن‌ها در اینجا نمایش داده می‌شود.
                                </p>

                            </div>

                        </div>

                    @endif

                </div>

            </aside>

        </section>


        {{-- ============================================================
            MOBILE MAP BUTTON
        ============================================================= --}}

        @if($mapData->count())

            <div class="mt-4 md:hidden">

                <button
                    type="button"
                    class="btn btn-secondary w-full"
                    @click="openMap()"
                >

                    <svg
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                        <circle cx="12" cy="10" r="2.5" />
                    </svg>

                    نمایش نقشه

                </button>

            </div>

        @endif


        {{-- ============================================================
            MOBILE MAP DRAWER
        ============================================================= --}}

        <div
            x-cloak
            x-show="mapOpen"
            x-transition.opacity
            class="fixed inset-0 z-[80] md:hidden"
        >

            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="closeMap()"
            ></div>


            <div
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="absolute inset-x-0 bottom-0 max-h-[90vh] overflow-hidden rounded-t-[2rem] border border-border bg-surface p-3 shadow-float"
            >

                <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-primary-300"></div>


                <div class="mb-3 flex items-center justify-between gap-3 px-2">

                    <div class="min-w-0">

                        <div class="text-[10px] font-black tracking-wider text-accent-600">
                            MAP
                        </div>

                        <h3
                            class="mt-1 truncate text-sm font-black text-content"
                            x-text="selectedMapSalon?.name || 'موقعیت سالن'"
                        ></h3>

                    </div>


                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-content-muted"
                        @click="closeMap()"
                        aria-label="بستن"
                    >
                        ×
                    </button>

                </div>


                <div class="overflow-hidden rounded-2xl border border-border">

                    <iframe
                        :src="
                            selectedMapSalon
                                ? 'https://www.google.com/maps?q='
                                    + selectedMapSalon.lat
                                    + ','
                                    + selectedMapSalon.lng
                                    + '&z=15&output=embed'
                                : ''
                        "
                        title="موقعیت سالن روی Google Maps"
                        class="h-[22rem] w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>

                </div>


                <div class="mt-3 grid grid-cols-2 gap-2">

                    <a
                        x-show="selectedMapSalon?.url"
                        :href="selectedMapSalon?.url"
                        class="btn btn-secondary"
                    >
                        صفحه سالن
                    </a>


                    <a
                        x-show="selectedMapSalon?.booking_url"
                        :href="selectedMapSalon?.booking_url"
                        class="btn btn-accent"
                    >
                        رزرو نوبت
                    </a>

                </div>


                <a
                    x-show="selectedMapSalon?.maps_url"
                    :href="selectedMapSalon?.maps_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-primary mt-2 w-full"
                >
                    باز کردن مسیر در Google Maps ↗
                </a>

            </div>

        </div>

    </div>

@endsection

