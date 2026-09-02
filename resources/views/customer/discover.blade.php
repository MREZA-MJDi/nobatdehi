@extends('layouts.app')

@section('title', 'پیدا کردن سالن')

@section(
    'meta_description',
    'سالن یا آرایشگر موردنظر خود را با نام، کد اختصاصی یا موقعیت پیدا کنید و نوبت بگیرید.'
)

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <div
        x-data="{
            mode: 'all',
            mapOpen: false,
            recentOpen: false,

            setMode(value) {
                this.mode = value
            },

            toggleMap() {
                this.mapOpen = !this.mapOpen
            }
        }"
        class="app-container"
    >

        {{-- ============================================================
            HERO
        ============================================================= --}}

        <section class="discover-hero relative">

            <div
                class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-[rgba(189,131,91,0.08)] blur-3xl"
            ></div>

            <div class="relative z-10">

                {{-- Eyebrow --}}

                <div class="discover-eyebrow">

                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-accent-100"
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

                    اسم، کد اختصاصی یا موقعیت سالن را جستجو کن؛
                    اطلاعات سالن، خدمات، امتیاز و مسیر دسترسی را قبل از رزرو ببین.

                </p>


                {{-- ====================================================
                    SEARCH PANEL
                ===================================================== --}}

                <div class="search-panel">

                    {{-- Search modes --}}

                    <div class="mb-3 flex items-center gap-1 overflow-x-auto pb-1">

                        <button
                            type="button"
                            @click="setMode('all')"
                            class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                            :class="
                                mode === 'all'
                                    ? 'bg-zinc-900 text-white'
                                    : 'text-zinc-500 hover:bg-zinc-100'
                            "
                        >
                            همه
                        </button>

                        <button
                            type="button"
                            @click="setMode('salon')"
                            class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                            :class="
                                mode === 'salon'
                                    ? 'bg-zinc-900 text-white'
                                    : 'text-zinc-500 hover:bg-zinc-100'
                            "
                        >
                            سالن‌ها
                        </button>

                        <button
                            type="button"
                            @click="setMode('barber')"
                            class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                            :class="
                                mode === 'barber'
                                    ? 'bg-zinc-900 text-white'
                                    : 'text-zinc-500 hover:bg-zinc-100'
                            "
                        >
                            آرایشگرها
                        </button>

                    </div>


                    <form
                        action="{{ route('salons.discover') }}"
                        method="GET"
                    >

                        <input
                            type="hidden"
                            name="type"
                            :value="mode === 'all' ? '' : mode"
                        >


                        <div class="search-grid">

                            {{-- Name Search --}}

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
                                    value="{{ request('q') }}"
                                    class="search-input"
                                    placeholder="اسم سالن یا آرایشگر را بنویس..."
                                    autocomplete="off"
                                >

                            </div>


                            {{-- Code Search --}}

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
                                    value="{{ request('code') }}"
                                    class="search-input"
                                    placeholder="کد اختصاصی سالن"
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

                                پیدا کن

                            </button>

                        </div>

                    </form>


                    {{-- Quick actions --}}

                    <div class="mt-3 flex flex-wrap gap-2">

                        <button
                            type="button"
                            class="filter-chip"
                            x-data
                            @click="toast.info('اسکن QR در مرحله بعد به دوربین موبایل وصل می‌شود.')"
                        >

                            <svg
                                width="15"
                                height="15"
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

                            اسکن QR سالن

                        </button>


                        <button
                            type="button"
                            class="filter-chip"
                            x-data
                            @click="toast.info('در نسخه بعدی از موقعیت فعلی شما استفاده می‌کنیم.')"
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


                    {{-- Recent searches --}}

                    <div
                        x-cloak
                        x-show="recentOpen"
                        x-transition
                        class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3"
                    >

                        <div class="mb-2 text-xs font-bold text-zinc-700">
                            جستجوهای اخیر
                        </div>

                        <div class="flex flex-wrap gap-2">

                            <span class="rounded-lg bg-white px-3 py-1.5 text-xs text-zinc-500 shadow-sm">
                                سالن فلان
                            </span>

                            <span class="rounded-lg bg-white px-3 py-1.5 text-xs text-zinc-500 shadow-sm">
                                SALON-X8K92
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ============================================================
            TOP STATS
        ============================================================= --}}

        <section class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-soft">

                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-accent-50 text-accent-600">

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

                <div class="text-xl font-black text-zinc-900">
                    +۱۲۰
                </div>

                <div class="mt-0.5 text-xs text-zinc-500">
                    سالن فعال
                </div>

            </div>


            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-soft">

                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700">

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

                <div class="text-xl font-black text-zinc-900">
                    +۴۸۰
                </div>

                <div class="mt-0.5 text-xs text-zinc-500">
                    آرایشگر
                </div>

            </div>


            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-soft">

                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600">

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

                <div class="text-xl font-black text-zinc-900">
                    +۲۵K
                </div>

                <div class="mt-0.5 text-xs text-zinc-500">
                    نوبت ثبت‌شده
                </div>

            </div>


            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-soft">

                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">

                    <svg
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="m12 3 2.8 5.6 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.5l6.2-.9L12 3Z" />
                    </svg>

                </div>

                <div class="text-xl font-black text-zinc-900">
                    ۴.۸
                </div>

                <div class="mt-0.5 text-xs text-zinc-500">
                    میانگین رضایت
                </div>

            </div>

        </section>


        {{-- ============================================================
            RESULTS + MAP
        ============================================================= --}}

        <section class="discover-layout">

            {{-- ========================================================
                RESULTS
            ========================================================= --}}

            <div class="results-column">

                <div class="results-header">

                    <div>

                        <div class="flex items-center gap-2">

                            <h2 class="section-title">
                                پیشنهادهای مناسب شما
                            </h2>

                            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-bold text-zinc-500">
                                نزدیک‌ترین
                            </span>

                        </div>

                        <p class="results-count mt-1">
                            سالن‌ها و آرایشگرهای منتخب را ببینید
                        </p>

                    </div>


                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        x-data
                        @click="toast.info('فیلترهای پیشرفته را بعداً اضافه می‌کنیم.')"
                    >

                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 6h16" />
                            <path d="M7 12h10" />
                            <path d="M10 18h4" />
                        </svg>

                        فیلتر

                    </button>

                </div>


                {{-- =====================================================
                    REAL DATA
                ====================================================== --}}

                @if(isset($salons) && $salons->count())

                    <div class="salon-grid">

                        @foreach($salons as $salon)

                            <article class="salon-card card-hover group">

                                {{-- Logo --}}

                                <div class="salon-logo">

                                    @if($salon->logo_path)

                                        <img
                                            src="{{ Storage::url($salon->logo_path) }}"
                                            alt="{{ $salon->name }}"
                                            class="h-full w-full object-cover"
                                        >

                                    @else

                                        {{ mb_substr($salon->name, 0, 1) }}

                                    @endif

                                </div>


                                <div class="salon-content">

                                    <div class="salon-topline">

                                        <div class="min-w-0">

                                            <div class="mb-1 flex flex-wrap items-center gap-1.5">

                                                @if($salon->is_active)

                                                    <span class="badge badge-success">
                                                        فعال
                                                    </span>

                                                @else

                                                    <span class="badge badge-danger">
                                                        غیرفعال
                                                    </span>

                                                @endif

                                                <span class="badge badge-accent">
                                                    سالن
                                                </span>

                                            </div>


                                            <h3 class="salon-name group-hover:text-accent-700">
                                                {{ $salon->name }}
                                            </h3>

                                            <p class="salon-type">
                                                سالن زیبایی
                                            </p>

                                        </div>


                                        @if(isset($salon->rating))

                                            <div class="salon-rating">

                                                <span class="text-amber-500">
                                                    ★
                                                </span>

                                                {{ number_format($salon->rating, 1) }}

                                            </div>

                                        @endif

                                    </div>


                                    <div class="salon-meta">

                                        @if($salon->city || $salon->district)

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

                                        @endif


                                        @if($salon->phone)

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

                                        @endif

                                    </div>


                                    <div class="salon-footer">

                                        <code
                                            class="salon-code"
                                            dir="ltr"
                                        >
                                            {{ $salon->code }}
                                        </code>


                                        <a
                                            href="{{ route('public.salons.show', $salon) }}"
                                            class="btn btn-primary btn-sm"
                                        >
                                            مشاهده سالن

                                            <svg
                                                width="14"
                                                height="14"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>

                                        </a>

                                    </div>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    @if(method_exists($salons, 'links'))

                        <div class="mt-5">
                            {{ $salons->links() }}
                        </div>

                    @endif


                @else

                    {{-- =================================================
                        PREMIUM DEMO RESULTS
                    ================================================== --}}

                    <div class="salon-grid">


                        {{-- CARD 01 --}}

                        <article class="salon-card card-hover group">

                            <div class="salon-logo">

                                <span>
                                    ن
                                </span>

                            </div>


                            <div class="salon-content">

                                <div class="salon-topline">

                                    <div class="min-w-0">

                                        <div class="mb-1 flex flex-wrap gap-1.5">

                                            <span class="badge badge-success">
                                                باز
                                            </span>

                                            <span class="badge badge-accent">
                                                محبوب
                                            </span>

                                        </div>

                                        <h3 class="salon-name group-hover:text-accent-700">
                                            سالن نوبان
                                        </h3>

                                        <p class="salon-type">
                                            سالن زیبایی و مراقبت
                                        </p>

                                    </div>


                                    <div class="salon-rating">

                                        <span class="text-amber-500">
                                            ★
                                        </span>

                                        ۴.۹

                                    </div>

                                </div>


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
                                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                            <circle cx="12" cy="10" r="2.5" />
                                        </svg>

                                        <span>
                                            تهران، سعادت‌آباد
                                        </span>

                                    </div>


                                    <div class="salon-meta-item">

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle cx="12" cy="12" r="8" />
                                            <path d="M12 8v4l2.5 2" />
                                        </svg>

                                        <span>
                                            امروز تا ساعت ۲۱:۰۰
                                        </span>

                                    </div>

                                </div>


                                <div class="salon-footer">

                                    <code class="salon-code" dir="ltr">
                                        SALON-X8K92
                                    </code>


                                    <a
                                        href="#"
                                        class="btn btn-primary btn-sm"
                                    >
                                        مشاهده سالن

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </article>



                        {{-- CARD 02 --}}

                        <article class="salon-card card-hover group">

                            <div class="salon-logo">

                                <span>
                                    A
                                </span>

                            </div>


                            <div class="salon-content">

                                <div class="salon-topline">

                                    <div class="min-w-0">

                                        <div class="mb-1 flex flex-wrap gap-1.5">

                                            <span class="badge badge-success">
                                                باز
                                            </span>

                                        </div>

                                        <h3 class="salon-name group-hover:text-accent-700">
                                            علی رضایی
                                        </h3>

                                        <p class="salon-type">
                                            آرایشگر حرفه‌ای
                                        </p>

                                    </div>


                                    <div class="salon-rating">

                                        <span class="text-amber-500">
                                            ★
                                        </span>

                                        ۴.۸

                                    </div>

                                </div>


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
                                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                            <circle cx="12" cy="10" r="2.5" />
                                        </svg>

                                        <span>
                                            تهران، جردن
                                        </span>

                                    </div>


                                    <div class="salon-meta-item">

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path d="M12 2v20" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                        </svg>

                                        <span>
                                            اصلاح، فید، استایل
                                        </span>

                                    </div>

                                </div>


                                <div class="salon-footer">

                                    <code class="salon-code" dir="ltr">
                                        BARBER-P7Q31
                                    </code>


                                    <a
                                        href="#"
                                        class="btn btn-primary btn-sm"
                                    >
                                        مشاهده آرایشگر

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </article>



                        {{-- CARD 03 --}}

                        <article class="salon-card card-hover group">

                            <div class="salon-logo">

                                <span>
                                    M
                                </span>

                            </div>


                            <div class="salon-content">

                                <div class="salon-topline">

                                    <div class="min-w-0">

                                        <div class="mb-1 flex flex-wrap gap-1.5">

                                            <span class="badge badge-success">
                                                باز
                                            </span>

                                        </div>

                                        <h3 class="salon-name group-hover:text-accent-700">
                                            متین استایل
                                        </h3>

                                        <p class="salon-type">
                                            سالن مردانه
                                        </p>

                                    </div>


                                    <div class="salon-rating">

                                        <span class="text-amber-500">
                                            ★
                                        </span>

                                        ۴.۷

                                    </div>

                                </div>


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
                                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                            <circle cx="12" cy="10" r="2.5" />
                                        </svg>

                                        <span>
                                            تهران، ونک
                                        </span>

                                    </div>


                                    <div class="salon-meta-item">

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path d="M12 2v20" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                        </svg>

                                        <span>
                                            از ۲۵۰ هزار تومان
                                        </span>

                                    </div>

                                </div>


                                <div class="salon-footer">

                                    <code class="salon-code" dir="ltr">
                                        SALON-M4T88
                                    </code>


                                    <a
                                        href="#"
                                        class="btn btn-primary btn-sm"
                                    >
                                        مشاهده سالن

                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </article>

                    </div>


                    <div class="mt-4 flex items-center gap-3 rounded-2xl border border-accent-100 bg-accent-50/60 p-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-accent-600 shadow-sm">

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M12 21s8-6 8-11a8 8 0 1 0-16 0c0 5 8 11 8 11Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>

                        </div>

                        <div class="min-w-0">

                            <p class="text-xs font-bold text-zinc-800">
                                موقعیت مکانی دقیق را اضافه می‌کنیم
                            </p>

                            <p class="mt-0.5 text-[11px] leading-6 text-zinc-500">
                                بعد از اتصال مختصات سالن‌ها، نتایج بر اساس فاصله واقعی مرتب می‌شوند.
                            </p>

                        </div>

                    </div>

                @endif

            </div>


            {{-- ========================================================
                MAP
            ========================================================= --}}

            <aside class="map-column">

                <div class="map-card overflow-hidden">

                    <div class="relative h-full min-h-[29rem] bg-[#ebe8e3]">

                        {{-- Map background --}}

                        <div
                            class="absolute inset-0"
                            style="
                                background-image:
                                    linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px);
                                background-size: 38px 38px;
                            "
                        ></div>


                        {{-- Road shapes --}}

                        <div class="absolute left-[8%] top-[35%] h-4 w-[90%] rotate-[-10deg] rounded-full bg-white/80 shadow-sm"></div>

                        <div class="absolute left-[30%] top-[8%] h-[90%] w-4 rotate-[18deg] rounded-full bg-white/75 shadow-sm"></div>

                        <div class="absolute left-[5%] top-[58%] h-3 w-[65%] rotate-[18deg] rounded-full bg-white/70"></div>

                        <div class="absolute left-[55%] top-[5%] h-[95%] w-2 rotate-[-38deg] rounded-full bg-white/65"></div>


                        {{-- Green areas --}}

                        <div class="absolute right-[8%] top-[18%] h-24 w-32 rounded-[40%] bg-green-100/60"></div>

                        <div class="absolute bottom-[12%] left-[8%] h-20 w-28 rounded-[40%] bg-green-100/50"></div>


                        {{-- Fake location pins --}}

                        <button
                            type="button"
                            class="group absolute right-[25%] top-[30%]"
                            title="سالن نوبان"
                        >

                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-zinc-900 text-white shadow-float transition group-hover:scale-110">

                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>

                            </span>

                        </button>


                        <button
                            type="button"
                            class="group absolute left-[24%] top-[47%]"
                            title="متین استایل"
                        >

                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-accent-600 text-white shadow-float transition group-hover:scale-110">

                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>

                            </span>

                        </button>


                        <button
                            type="button"
                            class="group absolute bottom-[19%] left-[48%]"
                            title="علی رضایی"
                        >

                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-zinc-700 text-white shadow-float transition group-hover:scale-110">

                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>

                            </span>

                        </button>


                        {{-- Map Controls --}}

                        <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">

                            <div class="map-label">

                                <span class="h-2 w-2 rounded-full bg-green-500"></span>

                                ۳ سالن نزدیک شما

                            </div>


                            <button
                                type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/70 bg-white/90 text-zinc-700 shadow-soft backdrop-blur transition hover:bg-white"
                                x-data
                                @click="toast.info('مکان‌یابی GPS بعداً فعال می‌شود.')"
                                aria-label="مکان من"
                            >

                                <svg
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="8" />
                                    <circle cx="12" cy="12" r="2.5" />
                                    <path d="M12 4v2" />
                                    <path d="M12 18v2" />
                                    <path d="M4 12h2" />
                                    <path d="M18 12h2" />
                                </svg>

                            </button>

                        </div>


                        {{-- Map Bottom Info --}}

                        <div class="absolute inset-x-3 bottom-3">

                            <div class="rounded-2xl border border-white/70 bg-white/92 p-3 shadow-float backdrop-blur-xl">

                                <div class="flex items-center justify-between gap-3">

                                    <div class="flex min-w-0 items-center gap-3">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-700">

                                            <svg
                                                width="18"
                                                height="18"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                                <circle cx="12" cy="10" r="2.5" />
                                            </svg>

                                        </div>

                                        <div class="min-w-0">

                                            <p class="truncate text-xs font-extrabold text-zinc-900">
                                                سالن نوبان
                                            </p>

                                            <p class="mt-0.5 truncate text-[10px] text-zinc-500">
                                                سعادت‌آباد، تهران
                                            </p>

                                        </div>

                                    </div>


                                    <a
                                        href="#"
                                        class="btn btn-primary btn-sm"
                                    >
                                        مسیر

                                        <svg
                                            width="13"
                                            height="13"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </aside>

        </section>


        {{-- ============================================================
            MOBILE MAP BUTTON
        ============================================================= --}}

        <div class="mt-4 md:hidden">

            <button
                type="button"
                class="btn btn-secondary w-full"
                @click="toggleMap()"
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

                <span x-text="mapOpen ? 'بستن نقشه' : 'نمایش روی نقشه'"></span>

            </button>

        </div>


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
                class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                @click="mapOpen = false"
            ></div>


            <div
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="absolute inset-x-0 bottom-0 overflow-hidden rounded-t-[2rem] bg-white p-3 shadow-float"
            >

                <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-zinc-200"></div>


                <div class="mb-3 flex items-center justify-between px-2">

                    <div>

                        <h3 class="text-sm font-black text-zinc-900">
                            سالن‌های روی نقشه
                        </h3>

                        <p class="mt-0.5 text-[10px] text-zinc-500">
                            موقعیت تقریبی سالن‌ها
                        </p>

                    </div>


                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600"
                        @click="mapOpen = false"
                        aria-label="بستن"
                    >
                        ×
                    </button>

                </div>


                <div class="map-card">

                    <div class="relative h-[20rem] overflow-hidden bg-[#ebe8e3]">

                        <div
                            class="absolute inset-0"
                            style="
                                background-image:
                                    linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px);
                                background-size: 34px 34px;
                            "
                        ></div>

                        <div class="absolute left-[10%] top-[40%] h-3 w-[85%] rotate-[-12deg] rounded-full bg-white"></div>

                        <div class="absolute left-[38%] top-[5%] h-[90%] w-3 rotate-[14deg] rounded-full bg-white"></div>

                        <div class="absolute right-[22%] top-[29%] flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-zinc-900 text-white shadow-float">

                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
